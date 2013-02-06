from django.template import Context, RequestContext
from django.views.decorators.csrf import csrf_protect
from django.core.context_processors import csrf
from django.shortcuts import render_to_response
from django.http import HttpResponseRedirect, HttpResponse, Http404
from django.contrib.auth import login
from django.contrib.auth.forms import UserCreationForm
from main.models import *

@csrf_protect
def index(request):
    user_form = UserCreationForm()
    c = RequestContext(request, {
            'page_title' : 'index',
            'registration_form' :  user_form,
        })
    return render_to_response('index.html', c)

@csrf_protect
def register(request, *args, **kwargs):
    """Register a new user"""
    if request.method == 'POST':
        new_user = User()
        user_form = UserCreationForm(request.POST, instance=new_user)
        if user_form.is_valid():
            user_form.save()
            login(request, new_user)
            #TODO: Redirect to a user page or something
            return render_to_response('register.html', RequestContext(request, {}))
        else:
            print "user_form not valid!"
    else:
        user_form = UserCreationForm()
        
    kwargs.update(csrf(request))
    c = RequestContext(request, dict(registration_form=user_form, **kwargs))
    return render_to_response('register.html', c)

def userpage(request):
    c = RequestContext(request, {
            'page_title' : 'user_page',
        })
    return render_to_response('user_page.html', c)

def item_info(request, item_id):
    try:
        item =  Items.objects.get(pk=item_id);
    except Items.DoesNotExist:
        raise Http404
    return render_to_response('itemDetail.html', {'item' : item} )

def items(request):

    c = RequestContext(request, {
    })
    return render_to_response('items.html', c)
