from forms import LoginForm

# Render the login form for all pages
def login_modal_form(request):
    if (request.method == 'POST'):
        return { 'login_form' : LoginForm(request.POST) }
    else:
        return { 'login_form' : LoginForm() }
