from main.models import Person

def some_view(request):
    get = request.GET
    form = PersonForm(request.GET)
    cleaned_data = form.cleaned_data
    cleaned_data['name']
    context = Context({"name" : cleaned_data['name']})
    return render_to_response(context, 'index.html')
