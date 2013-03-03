from forms import LoginForm

def login_modal_form(request):
    return { 'login_form' : LoginForm() }
