from Tkinter import *
import requests
import json
import sys
import usb.core
import usb.util
from qrtools import QR

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
		Button(buttonFrame, text="Lookup UIN", command=self.requestCardLookup).pack(side=TOP, padx=5, pady=5)
		buttonFrame.pack(fill=BOTH, side=BOTTOM)
		
		bottomFrame.pack(fill=BOTH, side=BOTTOM)

		#Entry Frame
		entryFrame = Frame(frame)

		self.itemEntryText = StringVar()
		self.itemEntryText.set('1')
		entryFrame.itemEntry = Entry(entryFrame, textvariable=self.itemEntryText, width=10).pack(side=LEFT, padx=5, pady=5)
		searchButton = Button(entryFrame, text="Search", command=self.search).pack(side=RIGHT, padx=5, pady=5)
		Button(buttonFrame, text="Lookup QR", command=self.requestQR).pack(side=TOP, padx=5, pady=5)
		entryFrame.pack(fill=BOTH, side=TOP)

		#Response Frame
		responseFrame = Frame(frame)
		self.responseText = StringVar()
		self.responseText.set("Waiting for a Search")
		self.responseLabel = Label(responseFrame, textvariable=self.responseText, justify=LEFT).pack(side=LEFT)
		responseFrame.pack(side=LEFT)

		#self frame
		self.pack(fill=BOTH, expand=1)

	def requestCardLookup(self):
		initCardReader(self)

	def requestQR(self):
		data = initQR(self)
		self.checkoutResponseLabelText.set(data)

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

def initQR(app):
	myCode = QR()
	myCode.decode_webcam()
	return myCode.data

def initCardReader(app):
	VENDOR_ID = 0x0801
	PRODUCT_ID = 0x0002
	DATA_SIZE = 337

	# find the MagTek reader

	device = usb.core.find(idVendor=VENDOR_ID, idProduct=PRODUCT_ID)

	if device is None:
	    sys.exit("Could not find MagTek USB HID Swipe Reader.")

	# make sure the hiddev kernel driver is not active

	if device.is_kernel_driver_active(0):
	    try:
	        device.detach_kernel_driver(0)
	    except usb.core.USBError as e:
	        sys.exit("Could not detatch kernel driver: %s" % str(e))

	# set configuration

	try:
	    device.set_configuration()
	    device.reset()
	except usb.core.USBError as e:
	    sys.exit("Could not set configuration: %s" % str(e))
	    
	endpoint = device[0][(0,0)][0]

	# wait for swipe

	data = []
	swiped = False
	print "Please swipe your card..."
	app.responseText.set("Please swipe your card...")

	while 1:
	    try:
	        data += device.read(endpoint.bEndpointAddress, endpoint.wMaxPacketSize)
	        if not swiped: 
	            print "Reading..."
	            app.responseText.set("Reading...")
	        swiped = True
	    except usb.core.USBError as e:
	        #print "Error %s" % e
	        #print e.args
	        #print "Swiped %s" % swiped
	        if e.args[0] == 110  and swiped:
	            if len(data) < DATA_SIZE:
	                print "Bad swipe, try again. (%d bytes)" % len(data)
	                app.responseText.set("Bad swipe, try again.")
	                print "Data: %s" % ''.join(map(chr, data))
	                data = []
	                swiped = False
	                continue
	            else:
	                break   # we got it!

	#print "Past Try"
	#print data


	# now we have the binary data from the MagReader! 

	enc_formats = ('ISO/ABA', 'AAMVA', 'CADL', 'Blank', 'Other', 'Undetermined', 'None')

	print "Card Encoding Type: %s" % enc_formats[data[6]]

	print "Track 1 Decode Status: %r" % bool(not data[0])
	print "Track 1 Data Length: %d bytes" % data[3]
	print "Track 1 Data: %s" % ''.join(map(chr, data[7:116]))

	print "Track 2 Decode Status: %r" % bool(not data[1])
	print "Track 2 Data Length: %d bytes" % data[4]
	print "Track 2 Data: %s" % ''.join(map(chr, data[117:226]))

	print "Track 3 Decode Status: %r" % bool(not data[2])
	print "Track 3 Data Length: %d bytes" % data[5]
	print "Track 3 Data: %s" % ''.join(map(chr, data[227:336]))

	# since this is a bank card we can parse out the cardholder data

	track = ''.join(map(chr, data[7:116]))

	info = {}

	i = track.find('^', 1)
	info['account_number'] = track[2:i].strip()
	j = track.find('/', i)
	info['last_name'] = track[i+1:j].strip()
	k = track.find('^', j)
	info['first_name'] = track[j+1:k].strip()
	info['exp_year'] = track[k+1:k+3]
	info['exp_month'] = track[k+3:k+5]

	#print "Bank card info: ", info
	app.currentUIN = info['account_number'][4:-3]
	app.responseText.set("UIN: %s" % app.currentUIN)

def main():

	root = Tk()
	root.geometry("500x300+300+300")
	app = TattGui(root)
	root.mainloop()

if __name__ == '__main__':
	main()
