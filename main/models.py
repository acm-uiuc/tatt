from django.db import models

class Items(models.Model):
    itemType = models.ForeignKey('ItemTypes')
    name = models.CharField(max_length=100)

class ItemTypes(models.Model):
    name = models.CharField(max_length=100)

class Attributes(models.Model):
    name = models.CharField(max_length=100)
