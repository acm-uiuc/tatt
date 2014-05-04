from main.models import *
from datetime import date, timedelta
from django.http import HttpResponse
from django.contrib.auth import authenticate
from django.shortcuts import get_object_or_404
import json

# pi_api

doesNotExistJson = json.dumps({
                "status_code" : 1,
                "error_message" : "Item Does Not Exist"
                })
unknownErrorJson = json.dumps({
                "status_code" : 1,
                "error_message" : "UnknownError"
                })
cantCheckOutJson = json.dumps({
                "status_code" : 1,
                "error_message" : "Can't Check Out"
                })
succesful = json.dumps({
            "status_code" : 0,
            "error_message" : None
            })


def getItemInfo(request, item_id):
    # Check existence
    try:
        item =  Item.objects.get(pk=item_id)
    except Item.DoesNotExist:
        return HttpResponse(doesNotExistJson, content_type="application/json")

    if request.method == 'GET':
        # Return Information
        return HttpResponse(
            json.dumps({
                "status_code" : 0,
                "error_message" : None,
                "item_type" : item.item_type.name,
                "name" : item.name,
                "location" : item.location,
                "checked_out_by" : str(item.checked_out_by),
                "due_date" : str(item.due_date),
                "username" : item.owner_id.username,
                "first_name" : item.owner_id.first_name,
                "last_name" : item.owner_id.last_name,
                "last_accounted_for" : str(item.last_accounted_for),
                "is_accounted_for" : item.is_accounted_for,
                "has_photo" : item.has_photo,
                "can_checkout" : item.can_checkout
            }), 
            content_type="application/json")
        
    return HttpResponse(unknownErrorJson, content_type="application/json")

def checkoutItem(request, item_id, UIN):
    # Check existence
    try:
        item =  Item.objects.get(pk=item_id)
    except Item.DoesNotExist:
        return HttpResponse(doesNotExistJson, content_type="application/json")

    if request.method == 'GET' and UIN is not None:
        # Checkout Book

        # Check availability
        if not item.can_checkout:
            return HttpResponse(cantCheckOutJson, content_type="application/json")

        # TODO: Convert UIN to USER
        user = get_object_or_404(User, pk=UIN)
        item.checked_out_by = user
        item.can_checkout = False
        item.last_accounted_for = date.today()
        #TODO: add a option to set how long people are allowed to borrow for
        item.due_date = item.last_accounted_for + timedelta(weeks=2)
        item.save()

        return HttpResponse(succesful, content_type="application/json")
    return HttpResponse(unknownErrorJson, content_type="application/json")

def checkinItem(request, item_id):
    # Check existence
    try:
        item =  Item.objects.get(pk=item_id)
    except Item.DoesNotExist:
        return HttpResponse(doesNotExistJson, content_type="application/json")

    if request.method == 'GET':
        # Checkout Book
        # TODO: make sure UIN maps to the user

        item.checked_out_by = None
        item.can_checkout = True
        item.last_accounted_for = date.today()
        #TODO: add option to set how long people are allowed to borrow for
        item.due_date = None
        item.save()

        return HttpResponse(succesful, content_type="application/json")
    return HttpResponse(unknownErrorJson, content_type="application/json")


