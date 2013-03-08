from django.db import models
from django.contrib.auth.models import User

class Item(models.Model):
    item_type = models.ForeignKey('ItemType')
    name = models.CharField(max_length=100)
    location = models.CharField(max_length = 100)
    due_date = models.DateField(null=True, blank=True)
    owner_id = models.ForeignKey(User, related_name='owner_id')
    checked_out_by = models.ForeignKey(User, related_name='checked_out_by', null=True, blank=True)
    last_accounted_for = models.DateField(auto_now_add = True)
    has_photo = models.BooleanField()
    can_checkout = models.BooleanField(default = False)


    def __unicode__(self):
        return self.name

class ItemType(models.Model):
    name = models.CharField(max_length=100)

    def __unicode__(self):
        return self.name

class Attribute(models.Model):
    name = models.CharField(max_length=100)
    item_type = models.ForeignKey(ItemType, related_name='item_type')
    
    def __unicode__(self):
        return self.name

class AttributeValue(models.Model):
    item = models.ForeignKey(Item)
    attribute = models.ForeignKey(Attribute)
    value = models.CharField(max_length=100)

    def __unicode__(self):
        return self.value
