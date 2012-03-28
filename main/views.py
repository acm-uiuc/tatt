from django.template import Context
from django.shortcuts import render_to_response

def index(request):
    c = Context({
            'page_title' : 'index',
        })
    return render_to_response('index.html', c)
