from django.contrib.auth.decorators import login_required
from django.template import RequestContext
from django.views.decorators.csrf import csrf_protect
from django.core.context_processors import csrf
from django.core import serializers
from django.shortcuts import render_to_response, redirect
from django.http import HttpResponseRedirect, Http404, HttpResponse
from django.contrib.auth import login, logout, authenticate
from main.models import *
from main.forms import *
from datetime import timedelta, date, datetime
import qrcode
import json

@csrf_protect
def index(request):

    c = RequestContext(request, {
        'page_title' : 'index',
		'badcred' : False
    })

    c['homepage'] = True
    if request.get_full_path().startswith('/?next='):
        c['badcred'] = True

    if request.method == 'POST':
        c['badcred'] = True
        login_form = LoginForm(request.POST)
        if login_form.is_valid():
            user_data = login_form.cleaned_data
            user = authenticate(username=user_data['username'], password=user_data['password'])
            if user is not None:
                if user.is_active:
                    login(request, user)
                    print "user logged in!" 
                    return HttpResponseRedirect('/items')
                else:
                    print "user not active"

    return render_to_response('index.html',context_instance=c)

def logout_view(request):
    logout(request)
    return redirect('/')

@csrf_protect
def register(request, *args, **kwargs):
    """Register a new user"""
    if request.method == 'POST':
        print request.POST
        user_form = UserForm(request.POST)
        if user_form.is_valid():

            user_form = user_form.cleaned_data
            new_user = User.objects.create_user(username=user_form['username'],
                                                password=user_form['password'],
                                                email=user_form['email'])
            new_user.first_name = user_form['first_name']
            new_user.last_name = user_form['last_name']

            new_user.save()
            print "new user created"
            # TODO: we should redirect to a login with a special header or something
            return HttpResponseRedirect('/')
        else:
            print "user_form not valid!"
    else:
        user_form = UserForm()
        
    kwargs.update(csrf(request))
    c = RequestContext(request, dict(registration_form=user_form, **kwargs))
    return render_to_response('register.html', c)

def userpage(request):
    c = RequestContext(request, {
            'page_title' : 'user_page',
        })
    return render_to_response('user_page.html', c)

def ajax(request, type1, data):
    if request.method == 'GET':
        if type1 == 'item_type':
            itemType = ItemType.objects.all().filter(id=data)
            attributes = serializers.serialize("json", Attribute.objects.all().filter(item_type=itemType))
            return HttpResponse(attributes, mimetype='application/json')
    
    return

@login_required()
def items(request):
    if request.method == 'GET':
        #TODO: parse search string and show new items
        pass

    items = Item.objects.filter(owner_id = request.user)
    c = RequestContext(request, {'items' : items})
    return render_to_response('items.html', c)

@login_required()
def toggle_accounted_all(request):
    items = Item.objects.filter(owner_id = request.user)
    for item in items:
        item.is_accounted_for = False
        item.save()
    return redirect('/items/')

@login_required()
def toggle_accounted(request, item_id):
    item = Item.objects.get(owner_id = request.user, id = item_id )
    if item is None:
        return
    item.is_accounted_for = not item.is_accounted_for
    if item.is_accounted_for:
        item.last_accounted_for = datetime.now().date()
    item.save()
    c = RequestContext(request, {'item' : item})
    return render_to_response('ajax/toggle_accounted_for.html', c)




@login_required()
def avail_items(request):
    if request.method == 'GET':
        query = request.GET.get('q', '')
        items = Item.objects.search(query)

    checkedoutitems = Item.objects.filter(checked_out_by = request.user)
    items = items.filter(can_checkout = True, checked_out_by = None).exclude(owner_id = request.user)
    c = RequestContext(request, {'checkedoutitems' : checkedoutitems, 'items' : items})
    return render_to_response('avail_items.html', c)

def item_info(request, item_id):
    try:
        item =  Item.objects.get(pk=item_id)
    except Item.DoesNotExist:
        #TODO: print out an error message or something about the item not exhisting
        raise Http404
    attr_vals = AttributeValue.objects.all().filter(item=item)

    #checks to see if the user is the owner
    owner = False
    checkedoutto = False
    if(request.user == item.owner_id):
        owner = True
    if(request.user == item.checked_out_by):
        checkedoutto = True
    if not (item.can_checkout or owner):
        return HttpResponseRedirect("/")

    c = RequestContext(request, {'item' : item, 'attr_vals' : attr_vals, 'owner' : owner, 'checkedoutto' : checkedoutto })
    return render_to_response('itemDetail.html', c )

def search(request, search_query):
    #TODO: Search the database and return a list of items, put in to requestcontext
    c = RequestContext(request, {'search_query' : search_query})
    return render_to_response('search.html', c)

def search_query(query_string):
    #TODO: Search the database and return all items matching the query string as a list
    item_lis = []
    return item_lis

def about(request):
    c = RequestContext(request, {})
    return render_to_response('about.html', c)

@login_required()
def checkout(request, item_id):
    try:
        item =  Item.objects.get(pk=item_id)
    except Item.DoesNotExist:
        #TODO: print out an error message or something about the item not exhisting
        raise Http404
    if request.method == 'POST':
        checkout_form = CheckoutForm(request.POST)
        if checkout_form.is_valid():
            item.checked_out_by = request.user
            item.last_accounted_for = date.today()
            #TODO: add a option to set how long people are allowed to borrow for
            item.due_date = item.last_accounted_for + timedelta(weeks=2)
            item.save()
            return HttpResponseRedirect('/items')
        else:
            print "checkout_form not valid"
    else:
        checkout_form = CheckoutForm()
    c = RequestContext(request, { 'checkout_form' : checkout_form, 'item' : item })
    return render_to_response('checkout.html', c)

@login_required()
def checkin(request, item_id):
    try:
        item =  Item.objects.get(pk=item_id)
    except Item.DoesNotExist:
        #TODO: print out an error message or something about the item not exhisting
        raise Http404

    item.checked_out_by = None
    item.last_accounted_for = date.today()
    #TODO: add a option to set how long people are allowed to borrow for
    item.due_date = None
    item.is_overdue = False;
    item.save() 
    c = RequestContext(request, {'item' : item })
    return HttpResponseRedirect('/items')


@login_required()
def make_avail(request, item_id):
    try:
        item = Item.objects.get(pk=item_id)
    except Item.DoesNotExist:
        #TODO: error message here
        raise Http404

    item.can_checkout = not item.can_checkout
    item.save()
    return HttpResponseRedirect('/items')

##### Views that are used to add to the database #####
@login_required()
def add_item(request):
    if request.method == 'POST':
        item_form = ItemForm(request.POST)
        if item_form.is_valid():
            item_form = item_form.cleaned_data
            reqList = request.POST.getlist('attr')
            reqList2 = request.POST.getlist('attr_pks')
            new_item = Item()
            new_attrVals = [AttributeValue() for nothing in range(0,len(reqList))]
            new_item.item_type = item_form['item_type']
            new_item.name = item_form['name']
            new_item.location = item_form['location']
            new_item.owner_id = request.user
            new_item.has_photo = False
            new_item.can_checkout = False
            new_item.save()
            i = 0
            #relatively slow but it works for now
            for new_attrVal in new_attrVals:
                new_attrVal.item = new_item
                new_attrVal.value = reqList[i]
                new_attrVal.attribute = Attribute.objects.get(pk=reqList2[i])
                new_attrVal.save()
                i += 1
            print "Item added to database"
            return redirect('/items/')
        else:
            print "item_form not valid"
    else:
        item_form = ItemForm()
    c = RequestContext(request, { 'item_form' : item_form })
    return render_to_response("add_item.html", c)

@login_required()
def rem_item(request):
    item = Item.objects.get(pk=int(request.REQUEST['id']))
    item.delete()
    payload = {'success' : True}
    return HttpResponse(json.dumps(payload), content_type='application/json')

def add_item_type(request):
    if request.method == 'POST':
        item_type_form = ItemTypeForm(request.POST)
        if item_type_form.is_valid():
            new_type = ItemType()
            new_type.name = item_type_form.cleaned_data['name']
            new_type.save()
            #create all the attributes
            for attr in request.session['tempattrs']:
                new_attr = Attribute()
                new_attr.name = attr
                new_attr.item_type = new_type
                new_attr.save()
            print "ItemType added to database"
        else:
            print "item_type_form not valid"
        del request.session['tempattrs']
        return HttpResponseRedirect("/additem/")
    else:
        #set up the temp attributes
        if not 'tempattrs' in request.session:
            request.session['tempattrs'] = []
        item_type_form = ItemTypeForm()
    c = RequestContext(request, { 'item_type_form' : item_type_form, 'temp_attrs' : request.session['tempattrs']})
    return render_to_response("add_item_type.html", c)

def add_attribute(request):
    if request.method == 'POST':
        attr_form = AttributeForm(request.POST)
        if attr_form.is_valid():
            #TODO: Like item, verify this works
            new_attr = Attribute()
            new_attr.name = attr_form.cleaned_data['name']
            new_attr.item_type = attr_form.cleaned_data['item_type']
            new_attr.save() 
            print "Attribute added to database"
        else:
            print "attr_form is not valid!"
        return HttpResponseRedirect("/items/")
    else:
        attr_form = AttributeForm()
    c = RequestContext(request, { 'attr_form' : attr_form })
    return render_to_response("add_attribute.html", c)

def add_temp_attr(request):
    attr = request.POST['attrname']
    tempattrs = request.session['tempattrs']
    if not attr in tempattrs:
        tempattrs.append(attr)
    request.session['tempattrs'] = tempattrs
    return HttpResponseRedirect("/additemtype/")

def rem_temp_attr(request, attr_num):
    tempattrs = request.session['tempattrs']
    tempattrs.pop(int(attr_num))
    request.session['tempattrs'] = tempattrs
    return HttpResponseRedirect("/additemtype/")
    

def add_attribute_value(request):
    if request.method == 'POST':
        attr_val_form = AttributeValueForm(request.POST)
        if attr_val_form.is_valid():
            #TODO: Like item, verify this works
            new_attr_val = AttributeValue()
            new_attr_val.attribute = attr_val_form.cleaned_data['attribute']
            new_attr_val.value = attr_val_form.cleaned_data['value']
            print "Attribute value added to database"
        else:
            print "attr_val_form is not valid!"
    return HttpResponseRedirect("/additem/")

def qrcodeGen(request,item_id):
    img = qrcode.make(item_id)
    response = HttpResponse(mimetype="image/png")
    img.save(response, "PNG")
    return response
