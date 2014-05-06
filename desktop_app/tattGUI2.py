from Tkinter import *
import requests
import json
import sys
import usb.core
import usb.util
from qrtools import QR

#debugging
import random

class TattGui(Frame):
	def __init__(self, parent):
		Frame.__init__(self, parent)
		self.parent = parent
		self.initUI()
		self.default()

	def initUI(self):
		self.parent.title("Track All the Things")

		# Main Frame
		frame = Frame(self, relief=RAISED, borderwidth=1)
		frame.pack(fill=BOTH, expand=1)

		#Welcome Frame
		welcomeFrame = Frame(frame)
		self.welcome = StringVar()
		Label(welcomeFrame, textvariable=self.welcome).pack(side=TOP)
		Frame(welcomeFrame, height=2, bd=1, relief=SUNKEN).pack(fill=X, padx=5, pady=5)
		welcomeFrame.pack(fill=BOTH, side=TOP, pady=5)

		#Bottom Frame
		bottomFrame = Frame(frame)

		Frame(bottomFrame, height=2, bd=1, relief=SUNKEN).pack(fill=X, padx=5, pady=5)

		self.lowerResponse = StringVar()
		Label(bottomFrame, textvariable=self.lowerResponse).pack(side=TOP, padx=5, pady=5)
		
		buttonFrame = Frame(bottomFrame)
		Button(buttonFrame, text="Check Out", command=self.checkout).pack(side=LEFT, padx=5)
		Button(buttonFrame, text="Return", command=self.returnItem).pack(side=LEFT, padx=5)
		Button(buttonFrame, text="Close", command=self.quit).pack(side=RIGHT, padx=5)
		buttonFrame.pack(fill=X, side=BOTTOM, pady=5)

		bottomFrame.pack(fill=X, side=BOTTOM)

		#Middle Frame
		middleFrame = Frame(frame)

		promptFrame = Frame(middleFrame)
		self.prompt = StringVar()
		Label(promptFrame, textvariable=self.prompt).pack(padx=5, pady=5)
		promptFrame.pack(fill=X)

		interactiveFrame = Frame(middleFrame)
		Button(interactiveFrame, text="Swipe In", command=self.swipeIn).pack(side=LEFT, padx=5)
		Button(interactiveFrame, text="Leave", command=self.leave).pack(side=LEFT, padx=5)
		Button(interactiveFrame, text="Scan QR", command=self.scanQR).pack(side=LEFT, padx=5)
		interactiveFrame.pack(side=TOP)

		textViewFrame = Frame(middleFrame)
		self.mainResponse = StringVar()
		Label(textViewFrame, textvariable=self.mainResponse, justify=LEFT).pack(side=LEFT)
		textViewFrame.pack(side=TOP, padx=5, pady=10)

		middleFrame.pack(fill=X)

		#Finish up self
		self.pack(fill=BOTH, expand=1)

	def default(self):
		#Initial Prompt
		self.prompt.set('Welcome, please click on \'Swipe In\' or \'Scan QR\' to get started!.')
		self.welcome.set('Welcome to Track All the Things!')
		self.lowerResponse.set('')
		self.mainResponse.set('')

		self.item = ''

		#Debug
		self.UIN = ''

	def checkout(self):
		print "Not Yet Implemented"
		if self.UIN == '':
			self.lowerResponse.set("You need to swipe in before you check something out.")
			self.prompt.set('Please click on \'Swipe In\' to get started.')
			return
		if self.item == '':
			self.lowerResponse.set("You haven't scanned an item yet!")
			self.prompt.set("Go ahead and scan an item\'s QR Code")
			return

		#TODO call api and let user know how it went

		url = 'http://localhost:8000/pi_api/checkoutItem/%s/%s' % (self.item, self.UIN)

		#TODO Try catch
		response = requests.get(url)

		if response.status_code != 200:
			self.lowerResponse.set("HTTP Error")
			self.prompt.set("There was an HTTP Error, try leaving and starting over.")
			return

		#Looks like http was successful
		rText = str(response.text)
		rJson = json.loads(rText)
		if rJson['status_code'] != 0:
			self.lowerResponse.set("Error: %s" % rJson['error_message'])
			self.prompt.set("Looks like there was an issue. Try scanning again.")
			return

		self.lowerResponse.set("Successfully Checked Out!")
		self.prompt.set("Your item was successfully checked out. You can leave or check out more!")

	def returnItem(self):
		print "Not Yet Implemented"
		if self.item == '':
			self.lowerResponse.set("You haven't scanned an item yet!")
			self.prompt.set("Go ahead and scan an item\'s QR Code")
			return

		#TODO call api and let user know how it went

	def swipeIn(self):
		print "Not Yet Implemented"
		#debuging
		self.UIN = '1'
		self.welcome.set("%s - User: %s" % (self.welcome.get(), self.UIN))

		if self.item == '':
			self.prompt.set('Click \'Scan QR\' to select the item you want to return or check out.')
		else:
			self.prompt.set('Successfully swiped you in!')

	def leave(self):
		print "Not Yet Implemented"
		self.default()


	def scanQR(self):
		print "Not Yet Implemented"

		#TODO make api call that return pkey of item

		#pretend key = 1
		key = random.randint(0,30)
		url = 'http://localhost:8000/pi_api/getInfo/%s/' % key

		#TODO try catch
		response = requests.get(url)
		if response.status_code != 200:
			self.lowerResponse.set("HTTP Error")
			self.prompt.set("Looks like there was an issue. Try scanning again.")
			return;

		#TODO try catch
		rText = str(response.text)
		rJson = json.loads(rText)
		if rJson['status_code'] != 0:
			self.lowerResponse.set("Error: %s" % rJson['error_message'])
			self.prompt.set("Looks like there was an issue. Try scanning again.")
			return

		#Should have been successful
		ownerData = 'Owner: \t%s %s' % (rJson['first_name'], rJson['last_name'])
		checkOutData = ''
		if rJson['can_checkout']:
			checkOutData = 'You can check this out'
		else:
			checkOutData = "This item has already been checked out by %s" % rJson['checked_out_by']
		
		typeData = 'Type: \t%s' % rJson['item_type']
		nameData = 'Name: \t%s' % rJson['name']
		locationData = 'Location: \t%s' % rJson['location']

		self.mainResponse.set('%s\n\n%s\n%s\n%s\n%s' % (checkOutData, ownerData, typeData, nameData, locationData))
		self.item = key
		self.prompt.set("Item looked up!")

def main():
	root = Tk()
	root.geometry("500x300+300+300")
	app = TattGui(root)
	root.mainloop()

if __name__ == '__main__':
	main()