from django.conf.urls.defaults import patterns, include, url

# Uncomment the next two lines to enable the admin:
# from django.contrib import admin
# admin.autodiscover()

urlpatterns = patterns('',
    # Examples:
    # url(r'^$', 'tatt.views.home', name='home'),
    # url(r'^tatt/', include('tatt.foo.urls')),
    url(r'^index/', 'main.views.index'),
    url(r'', 'django.views.generic.simple.redirect_to', {'url': '/index/'}),
    url(r'/', 'django.views.generic.simple.redirect_to', {'url': '/index/'}),
    url(r'^favicon\.ico$', 'django.views.generic.simple.redirect_to', {'url': '/static/images/favicon.ico'}),

    # Uncomment the admin/doc line below to enable admin documentation:
    # url(r'^admin/doc/', include('django.contrib.admindocs.urls')),

    # Uncomment the next line to enable the admin:
    # url(r'^admin/', include(admin.site.urls)),
)
