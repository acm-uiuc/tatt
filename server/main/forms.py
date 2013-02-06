from django.forms import ModelForm
from models import *

class UserForm():
    class Meta:
        model = User
        fields = ( 'username', 'first_name', 'last_name' )
