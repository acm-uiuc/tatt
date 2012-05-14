from django.template import Context
from django.shortcuts import render_to_response
from django.http import HttpResponseRedirect
from django.template import RequestContext

def index(request):
    c = RequestContext(request, {
            'page_title' : 'index',
        })
    return render_to_response('index.html', c)
