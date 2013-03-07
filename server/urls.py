from django.conf.urls import patterns, include, url
from django.contrib import admin

# Uncomment the next two lines to enable the admin:
# from django.contrib import admin
admin.autodiscover()

urlpatterns = patterns('',
    # Examples:
    # url(r'^$', 'tatt.views.home', name='home'),
    # url(r'^tatt/', include('tatt.foo.urls')),
    url(r'^$', 'main.views.index'),
    url(r'^logout$', 'main.views.logout_view'),
    url(r'^favicon\.ico$', 'django.views.generic.simple.redirect_to', {'url': '/static/images/favicon.ico'}),
    url(r'^register/$', 'main.views.register'),
    url(r'^about/$', 'main.views.about'),

	# User page
	url(r'^userpage/$', 'main.views.userpage'),

    # Item related pages
    url(r'^items/$', 'main.views.items'),
    url(r'^item/(?P<item_id>\d+)/$', 'main.views.item_info'),
    url(r'^checkout/(?P<item_id>\d+)/$', 'main.views.checkout'),
    url(r'^additem/$','main.views.add_item'),
    
    # Search page
    url(r'^search/(?P<search_query>[a-zA-Z]+)', 'main.views.search'),

    # Uncomment the admin/doc line below to enable admin documentation:
    # url(r'^admin/doc/', include('django.contrib.admindocs.urls')),

    # Uncomment the next line to enable the admin:
    url(r'^admin/', include(admin.site.urls)),
)
