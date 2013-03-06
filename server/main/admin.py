from main.models import *
from django.contrib import admin

class ItemAdmin(admin.ModelAdmin):
    list_display = ('item_type', 'name')


class AttributeValueAdmin(admin.ModelAdmin):
    list_display = ('item', 'attribute', 'value')

admin.site.register(Item, ItemAdmin)
admin.site.register(ItemType)
admin.site.register(Attribute)
admin.site.register(AttributeValue, AttributeValueAdmin)
