from django.db import models
from django.contrib.auth.models import User

class Items(models.Model):
    item_type = models.ForeignKey(ItemTypes)
    name = models.CharField(max_length=100)
    location = models.CharField(max_length = 100)
    due_date = models.DateField(auto_now_add = True)
    owner_id = models.ManyToManyField(User)
    last_accounted_for = models.ManyToManyField(User)
    has_photo = models.CharField(max_length = 100)
    checked_out_by = models.ManyToManyField(User)

class ItemTypes(models.Model):
    name = models.CharField(max_length=100)

class Attributes(models.Model):
    name = models.CharField(max_length=100)
    value = models.CharField(max_length=100)
    item_type = models.ForeignKey(ItemTypes)
