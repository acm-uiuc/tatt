from django.db import models
from django.contrib.auth.models import User

class Items(models.Model):
    item_type = models.ForeignKey('ItemTypes')
    name = models.CharField(max_length=100)
    location = models.CharField(max_length = 100)
    due_date = models.DateField(auto_now_add = True)
    owner_id = models.ManyToManyField(User, related_name='owner_id')
    checked_out_by = models.ManyToManyField(User, related_name='checked_out_by')
    last_accounted_for = models.DateField()
    has_photo = models.CharField(max_length = 100)

class ItemTypes(models.Model):
    name = models.CharField(max_length=100)

class Attributes(models.Model):
    name = models.CharField(max_length=100)
    value = models.CharField(max_length=100)
    item_type = models.ForeignKey(ItemTypes)
