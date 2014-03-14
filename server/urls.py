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
    url(r'^toggleAccounted/(?P<item_id>\d+)$', 'main.views.toggle_accounted'),
    url(r'^toggleAccounted/$', 'main.views.toggle_accounted_all'),
    url(r'^deleteItem/$', 'main.views.rem_item'),
    url(r'^additem/$','main.views.add_item'),
    url(r'^addattr/$','main.views.add_attribute'),
    url(r'^additemtype/$','main.views.add_item_type'),

    url(r'^addtempattr/$','main.views.add_temp_attr'),
    url(r'^remtempattr/(?P<attr_num>\d+)/$','main.views.rem_temp_attr'),

    url(r'^make_avail/(?P<item_id>\d+)/$', 'main.views.make_avail'),
    url(r'^ajax/(?P<type1>[a-z_A-Z]+)/(?P<data>[0-9]+)/$', 'main.views.ajax'),
    url(r'^avail_items/$', 'main.views.avail_items'),
    url(r'^checkin/(?P<item_id>\d+)/$', 'main.views.checkin'),

    # Search page
    url(r'^search/(?P<search_query>[a-zA-Z]+)', 'main.views.search'),

    url(r'^qrcode/(?P<item_id>\d+)$', 'main.views.qrcodeGen'),

    # Rasberry Pi API
    url(r'^pi_api/getInfo/(?P<item_id>\d+)/$', 'main.pi_api.getItemInfo'),
    url(r'^pi_api/checkoutItem/(?P<item_id>\d+)/(?P<UIN>\d+)/$', 'main.pi_api.checkoutItem'),
    url(r'^pi_api/checkinItem/(?P<item_id>\d+)/$', 'main.pi_api.checkinItem'),

    # Uncomment the admin/doc line below to enable admin documentation:
    # url(r'^admin/doc/', include('django.contrib.admindocs.urls')),

    # Uncomment the next line to enable the admin:
    url(r'^admin/', include(admin.site.urls)),
)
