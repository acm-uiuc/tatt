from django.db import models
from django.db.models import Q
from django.contrib.auth.models import User

class ItemManager(models.Manager):
    def search(self, search_query):
        terms = [term.strip() for term in search_query.split()]

        queries = {}

        for term in terms:
                things = term.split(':');
                if len(things) == 1:
                    if not 'name' in queries:
                        queries['name'] = things[0]
                    else:
                        queries['name'] = queries['name'] + ' ' + things[0]
                else:
                    queries[things[0]] = things[1]

        qs = self.get_query_set()

        for kind, value in queries.iteritems():
            if kind == 'type':
                qs = qs.filter(item_type__name__icontains=value)
            elif kind == 'location':
                qs = qs.filter(location__icontains=value)
            elif kind == 'id' and value.isdigit():
                qs = qs.filter(pk__exact=value)
            elif kind == 'owner':
                qs = qs.filter(owner_id__username__icontains=value)
            elif any(kind in s.split()[0].lower() for s in Attribute.objects.values_list('name', flat=True)):
                qs = qs.filter(attributevalue__attribute__name__icontains=kind, attributevalue__value__icontains=value)
            else:
                qs = qs.filter(name__icontains=value)

        if len(terms) == 0:
            return qs.filter()

        return qs

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
    is_overdue = models.BooleanField(default = False)
    
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
