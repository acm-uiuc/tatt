from django.conf.urls.defaults import patterns, include, url
from django.contrib import admin

# Uncomment the next two lines to enable the admin:
# from django.contrib import admin
admin.autodiscover()

urlpatterns = patterns('',
    # Examples:
    # url(r'^$', 'tatt.views.home', name='home'),
    # url(r'^tatt/', include('tatt.foo.urls')),
<<<<<<< HEAD
    url(r'^index/', 'main.views.index'),
    #url(r'', 'django.views.generic.simple.redirect_to', {'url': '/index/'}),
    #url(r'/', 'django.views.generic.simple.redirect_to', {'url': '/index/'}),
    #url(r'^favicon\.ico$', 'django.views.generic.simple.redirect_to', {'url': '/static/images/favicon.ico'}),
=======
    url(r'^index/$', 'main.views.index'),
    url(r'^favicon\.ico$', 'django.views.generic.simple.redirect_to', {'url': '/static/images/favicon.ico'}),
    url(r'^register/$', 'main.views.register'),

    # Item view pages
    url(r'^items/$', 'main.views.items'),
    url(r'^item/(?P<item_id>\d+)/$', 'main.views.item_info'),

    url(r'^$', 'django.views.generic.simple.redirect_to', {'url': '/index/'}),
    url(r'^/$', 'django.views.generic.simple.redirect_to', {'url': '/index/'}),
>>>>>>> d5b542e9f56b0b56c7e812a03b4fb26f04eef298

    # Uncomment the admin/doc line below to enable admin documentation:
    # url(r'^admin/doc/', include('django.contrib.admindocs.urls')),

    # Uncomment the next line to enable the admin:
    url(r'^admin/', include(admin.site.urls)),
)
