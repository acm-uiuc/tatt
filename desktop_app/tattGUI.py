from Tkinter import *
import requests
import json

class TattGui(Frame):
	def __init__(self, parent):
		Frame.__init__(self, parent)

		self.parent = parent
		
		self.initUI()

	def initUI(self):
		self.parent.title("Track All the Things")

		#Setup
		self.itemNumber = '1' # Default search
		self.viableCheckout = True

		#Main Frame
		frame = Frame(self, relief=RAISED, borderwidth=1)
		frame.pack(fill=BOTH, expand=1)

		#Welcome Frame
		welcomeFrame = Frame(frame)
		welcome = Label(welcomeFrame, text="Welcome to Track all the Things!")
		welcome.pack(side=TOP)
		welcomeFrame.pack(fill=BOTH, side=TOP)

		#Bottom Frame
		bottomFrame = Frame(frame)

		self.checkoutResponseLabelText = StringVar()
		self.checkoutResponseLabel = Label(bottomFrame, textvariable=self.checkoutResponseLabelText, justify=LEFT).pack(side=TOP, pady=5)

		buttonFrame = Frame(bottomFrame)
		Button(buttonFrame, text="Close", command=self.quit).pack(side=RIGHT, padx=5, pady=5)
		Button(buttonFrame, text="Check Out", command=self.checkout).pack(side=LEFT, padx=5, pady=5)
		buttonFrame.pack(fill=BOTH, side=BOTTOM)
		
		bottomFrame.pack(fill=BOTH, side=BOTTOM)

		#Entry Frame
		entryFrame = Frame(frame)

		self.itemEntryText = StringVar()
		self.itemEntryText.set('1')
		entryFrame.itemEntry = Entry(entryFrame, textvariable=self.itemEntryText, width=10).pack(side=LEFT, padx=5, pady=5)
		searchButton = Button(entryFrame, text="Search", command=self.search).pack(side=RIGHT, padx=5, pady=5)
		entryFrame.pack(fill=BOTH, side=TOP)

		#Response Frame
		responseFrame = Frame(frame)
		self.responseText = StringVar()
		self.responseText.set("Waiting for a Search")
		self.responseLabel = Label(responseFrame, textvariable=self.responseText, justify=LEFT).pack(side=LEFT)
		responseFrame.pack(side=LEFT)

		#self frame
		self.pack(fill=BOTH, expand=1)

		

	def search(self):

		self.checkoutResponseLabelText.set('')

		self.itemNumber = self.itemEntryText.get()
		print(self.itemNumber)
		url = 'http://localhost:8000/pi_api/getInfo/%s/' % self.itemNumber
		print(url)
  		response = requests.get(url)
  		if response.status_code != 200:
  			self.responseText.set('HTTP Error')
  			self.viableCheckout = False
  			return

		rText = str(response.text)
		rJson = json.loads(rText)
		if rJson['status_code'] != 0:
			self.responseText.set('Error %s' % (rJson['error_message']))
			self.viableCheckout = False
		else:

			#self.responseText.set('Checked out by: %s' % rJson['checked_out_by'])
			ownerData = 'Owner: \t%s %s' % (rJson['first_name'], rJson['last_name'])
			checkOutData = ''
			if rJson['can_checkout']:
				checkOutData = 'You can check this out'
				self.viableCheckout = True
			else:
				checkOutData = "This item has already been checked out by %s" % rJson['checked_out_by']
				self.viableCheckout = False
			
			typeData = 'Type: \t%s' % rJson['item_type']
			nameData = 'Name: \t%s' % rJson['name']
			locationData = 'Location: \t%s' % rJson['location']

			self.responseText.set('%s\n\n%s\n%s\n%s\n%s' % (checkOutData, ownerData, typeData, nameData, locationData))

	def checkout(self):
		print('Checkout called')

		if self.viableCheckout == False:
			self.checkoutResponseLabelText.set("Can't Check Out.")
			return

		#Run Checkout
		UIN = 1
		url = 'http://localhost:8000/pi_api/checkoutItem/%s/%s' % (self.itemNumber, UIN)
		print('requesting: %s' % url)
		response = requests.get(url)

		#Checking for errors
		message = ''
		if response.status_code != 200:
			message = 'Failed to make request'
		else:
			rJson = json.loads(str(response.text))
			result = 'Checkout Successful!'
			if rJson['status_code'] == 1:
				result = 'Error: %s' % rJson['error_message']

		self.checkoutResponseLabelText.set(result)

def main():

	root = Tk()
	root.geometry("500x300+300+300")
	app = TattGui(root)
	root.mainloop()

if __name__ == '__main__':
	main()
