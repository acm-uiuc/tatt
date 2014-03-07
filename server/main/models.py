from django.db import models
from django.db.models import Q
from django.contrib.auth.models import User
import operator

class ItemManager(models.Manager):
    def search(self, search_query):
        terms = [term.strip() for term in search_query.split()]
            
        q_objects = []

        for term in terms:
            q_objects.append(Q(name__icontains=term))

        qs = self.get_query_set()

        if len(terms) == 0:
            return qs.filter()

        return qs.filter(reduce(operator.or_, q_objects))

class Item(models.Model):
    item_type = models.ForeignKey('ItemType')
    name = models.CharField(max_length=100)
    location = models.CharField(max_length = 100)
    due_date = models.DateField(null=True, blank=True)
    owner_id = models.ForeignKey(User, related_name='owner_id')
    checked_out_by = models.ForeignKey(User, related_name='checked_out_by', null=True, blank=True)
    last_accounted_for = models.DateField(auto_now_add = True)
    is_accounted_for = models.BooleanField(default = True)
    has_photo = models.BooleanField()
    can_checkout = models.BooleanField(default = False)
    
    objects = ItemManager()

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
