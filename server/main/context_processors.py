from forms import LoginForm
import hashlib
import time

# Render the login form for all pages
from main.models import Item

def login_modal_form(request):
    if (request.method == 'POST'):
        return { 'login_form' : LoginForm(request.POST) }
    else:
        return { 'login_form' : LoginForm() }

def anti_cache_hash(request):
    m = hashlib.md5()
    m.update( str(time.time()) )
    return { 'cache_hash' : m.hexdigest() }

def count_checkouts(request):
    checkouts = 0
    if(request.user.is_active):
        checkouts = Item.objects.filter(checked_out_by = request.user).count()
    return {'checkout_count' : checkouts}