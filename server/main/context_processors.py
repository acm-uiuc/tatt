from forms import LoginForm

# Render the login form for all pages
def login_modal_form(request):
    if (request.method == 'POST'):
        return { 'login_form' : LoginForm(request.POST) }
    else:
        return { 'login_form' : LoginForm() }

def baseurl(request):
    """
    Return a BASE_URL template context for the current request.
    """
    if request.is_secure():
        scheme = 'https://'
    else:
        scheme = 'http://'
        
    return {'BASE_URL': scheme + request.get_host(),}
