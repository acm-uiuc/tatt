from django.core.management.base import BaseCommand, CommandError
from django.utils.datetime_safe import datetime
from django.core.mail import send_mail
from main.models import *
from datetime import date

class Command(BaseCommand):
    help = 'Checks the item database and emails users with overdue items.'

    def handle(self, *args, **options):
        for i in Item.objects.all():
            # print "Looking at " + i.name;
            
            if i.due_date != None:
                if i.due_date <= date.today() and not i.is_overdue:
                    print "Mailing " + i.checked_out_by.username + " at " + i.checked_out_by.email + " about " + i.name;
                    # i.is_overdue = True;
                    send_mail('Track All The Things: Item Overdue', 'The item ' + i.name + ' is overdue. Please return it soon.', 'tatt@acm.illinois.edu', [i.checked_out_by.email], fail_silently=False)


