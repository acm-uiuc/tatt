from django import forms
from django.forms import ModelForm
from models import *
from crispy_forms.helper import FormHelper
from crispy_forms.layout import *
import datetime


class ItemForm(forms.ModelForm):
    def __init__(self, *args, **kwargs):
        super(ItemForm, self).__init__(*args, **kwargs)
        self.helper = FormHelper(self)
        self.helper.form_method = 'post'
        self.helper.add_input(Submit('submit', 'Add Item'))

    class Meta:
        model = Item
        fields = ('item_type', 'name', 'location')

class ItemTypeForm(forms.ModelForm):
    def __init__(self, *args, **kwargs):
        super(ItemTypeForm, self).__init__(*args, **kwargs)
        self.helper = FormHelper(self)
        self.helper.form_method = 'post'
        self.helper.add_input(Submit('submit', 'Add Item Type'))

    class Meta:
        model = ItemType
        fields = ('name', 'attributes')

class AttributeValueForm(forms.ModelForm):
    def __init__(self, *args, **kwargs):
        super(AttributeValueForm, self).__init__(*args, **kwargs)
        self.helper = FormHelper(self)
        self.helper.form_method = 'post'
        self.helper.add_input(Submit('submit', 'Add Attribute'))

    class Meta:
        model = AttributeValue
        fields = ('item', 'attribute', 'value')

class AttributeForm(forms.ModelForm):
    def __init__(self, *args, **kwargs):
        super(AttributeForm, self).__init__(*args, **kwargs)
        self.helper = FormHelper(self)
        self.helper.form_method = 'post'
        self.helper.add_input(Submit('submit', 'Add Attribute'))

    def clean(self):
        form_get_name = self.cleaned_data.get('name')
        name = Attribute.objects.all().filter(name=form_get_name)

        if name:
            raise forms.ValidationError("Name: " + form_username + " already exists!")  
        return self.cleaned_data

    class Meta:
        model = Attribute
        fields = ('name',)

class UserForm(forms.ModelForm):
    password = forms.CharField(
        label = "Password",
        max_length = 200,
        required = True,
        widget=forms.PasswordInput
        )
    password2 = forms.CharField(
        label = "Confirm Password",
        max_length = 200,
        required = True,
        widget=forms.PasswordInput
        )
    def __init__(self, *args, **kwargs):
        super(UserForm, self).__init__(*args, **kwargs)

        self.helper = FormHelper(self)
        self.helper.form_method = 'post'
        self.helper.add_input(Submit('submit', 'Submit'))

    def clean(self):
        form_username = self.cleaned_data.get('username')
        user = User.objects.all().filter(username=form_username)
        #print user
        if user:
            raise forms.ValidationError("User: " + form_username + " already exists!")  
        password = self.cleaned_data.get('password')
        password2 =  self.cleaned_data.get('password2')
        if password and password2 and password != password2:
            raise forms.ValidationError("Passwords don't match!")
        return self.cleaned_data

    class Meta:
        model = User 
        fields = ('username', 'first_name', 'last_name')

class LoginForm(forms.Form):
    username = forms.CharField(
        label = "Login"
        )
    password = forms.CharField(
        label = "Password",
        widget=forms.PasswordInput
        )

    def __init__(self, *args, **kwargs):
        super(LoginForm, self).__init__(*args, **kwargs)

        self.helper = FormHelper(self)
        self.helper.form_method = 'post'

        #self.helper.layout = Layout(
        #    Div('class="modal-body"',
        #        Div('class="control-group"',
        #        Fieldset('Login')
        #        ),
        #        Div('class="control-group"',
        #        Fieldset('Password')
        #        )
        #    )
        #)

        self.helper.add_input(Submit('submit', 'Login'))
