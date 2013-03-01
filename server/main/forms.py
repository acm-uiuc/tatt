from django import forms
from django.forms import ModelForm
from models import *
from crispy_forms.helper import FormHelper
from crispy_forms.layout import *

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

        # If you pass FormHelper constructor a form instance
        # It builds a default layout with all its fields
        self.helper = FormHelper(self)
        self.helper.form_method = 'POST'
        self.helper.add_input(Submit('submit', 'Submit'))

    def clean(self):
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
        self.helper.form_method = 'POST'

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
