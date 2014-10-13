'''
Created By: Eric Rosenberg

Tkinter GUI, ICard, and QR Code processing.

cardRead processing with the help of Micah Carrick - check out http://www.micahcarrick.com/credit-card-reader-pyusb.html

Dependent on PyUSB 1.0 branch
'''

from Tkinter import *
import requests
import json
import sys
import usb.core
import usb.util
import time
from qrtools import QR

#debugging
import random

class TattGui(Frame):
	def __init__(self, parent, domainAndPort):
		Frame.__init__(self, parent)
		self.parent = parent
		self.domainAndPort = domainAndPort
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
		self.prompt.set('Welcome, please click on \'Swipe In\' or \'Scan QR\' to get started!.')
		self.welcome.set('Welcome to Track All the Things!')
		self.lowerResponse.set('')
		self.mainResponse.set('')
		self.item = ''
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

		url = 'http://%s/pi_api/checkoutItem/%s/%s' % (self.domainAndPort, self.item, self.UIN)

		try:
			response = requests.get(url)

			if response.status_code != 200:
				raise Exception('Bad HTTP Response')

			#Looks like http was successful
			rText = str(response.text)
			rJson = json.loads(rText)

		except:
			self.lowerResponse.set("HTTP Error")
			self.prompt.set("There was an HTTP Error, try leaving and starting over.")
			return

		if rJson['status_code'] != 0:
			self.lowerResponse.set("Error: %s" % rJson['error_message'])
			self.prompt.set("Looks like there was an issue. Try scanning again.")
			return

		self.lowerResponse.set("Successfully Checked Out!")
		self.prompt.set("Your item was successfully checked out. You can leave or check out more!")

	def returnItem(self):
		if self.item == '':
			self.lowerResponse.set("You haven't scanned an item yet!")
			self.prompt.set("Go ahead and scan an item\'s QR Code")
			return

		url = 'http://%s/pi_api/checkinItem/%s' % (self.domainAndPort, self.item)

		try:
			response = requests.get(url)

			if response.status_code != 200:
				raise Exception('Bad HTTP Response')

			rText = str(response.text)
			rJson = json.loads(rText)

		except:
			self.lowerResponse.set("HTTP Error")
			self.prompt.set("There was an HTTP Error, try leaving and starting over.")
			return

		if rJson['status_code'] != 0:
			self.lowerResponse.set("Error: %s" % rJson['error_message'])
			self.prompt.set("Looks like there was an issue. Try scanning again.")
			return

		self.lowerResponse.set("Successfully Returned!")
		self.prompt.set("Your item was successfully retuned.")

	def swipeIn(self):
		#Only if card reader in
		UIN = cardRead(self.lowerResponse)

		if UIN == -1:
			return

		self.UIN = UIN

		#debuging
		#self.UIN = '1'

		self.welcome.set("%s - User: %s" % (self.welcome.get(), self.UIN))

		if self.item == '':
			self.prompt.set('Click \'Scan QR\' to select the item you want to return or check out.')
		else:
			self.prompt.set('Successfully swiped you in!')

	def leave(self):
		self.default()

	def scanQR(self):
		#Only if QR plugged in
		key = readQR()

		if key == -1:
			self.lowerResponse.set("Either you close the window or webcam is having issues. Maybe ask someone for help.")
			return
		#Debug, random search
		#key = random.randint(0,30)

		url = 'http://%s/pi_api/getInfo/%s/' % (self.domainAndPort, key)

		try:
			response = requests.get(url)

			if response.status_code != 200:
				raise Exception('Bad HTTP Response')

			rText = str(response.text)
			rJson = json.loads(rText)
			
		except:
			self.lowerResponse.set("HTTP Error")
			self.prompt.set("Looks like there was an issue. Try scanning again.")
			return;

		if rJson['status_code'] != 0:
			self.lowerResponse.set("Error: %s" % rJson['error_message'])
			self.prompt.set("Looks like there was an issue. Try scanning again.")
			return

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
		self.lowerResponse.set('')

def readQR():
	future = time.time() + 10
	ret = 'NULL'
	while time.time() < future and ret == 'NULL':
		myCode = QR()
		myCode.decode_webcam()
		ret = myCode.data

	if ret == 'NULL':
		return -1
	return ret

def cardRead(label):
	VENDOR_ID = 0x0801
	PRODUCT_ID = 0x0002
	DATA_SIZE = 337

	# find the MagTek reader

	device = usb.core.find(idVendor=VENDOR_ID, idProduct=PRODUCT_ID)

	if device is None:
	    #sys.exit("Could not find MagTek USB HID Swipe Reader.")
	    label.set("Could not find MagTek USB HID Swipe Reader.")
	    return -1

	# make sure the hiddev kernel driver is not active

	if device.is_kernel_driver_active(0):
	    try:
	        device.detach_kernel_driver(0)
	    except usb.core.USBError as e:
	        #sys.exit("Could not detatch kernel driver: %s" % str(e))
	        label.set("Could not detatch kernel driver: %s" % str(e))
	        return -1

	# set configuration

	try:
	    device.set_configuration()
	    device.reset()
	except usb.core.USBError as e:
	    #sys.exit("Could not set configuration: %s" % str(e))
	    label.set("Could not set configuration: %s" % str(e))
	    return -1
	    
	endpoint = device[0][(0,0)][0]

	# wait for swipe

	data = []
	swiped = False
	label.set("Please swipe your card...")

	future = time.time() + 10

	while 1:
	    try:
	        data += device.read(endpoint.bEndpointAddress, endpoint.wMaxPacketSize)
	        if not swiped: 
	            label.set("Reading...")
	        swiped = True
	    except usb.core.USBError as e:
	        if e.args[0] == 110  and swiped:
	            if len(data) < DATA_SIZE:
	                #print "Bad swipe, try again. (%d bytes)" % len(data)
	                label.set("Bad swipe, try again.")
	                #print "Data: %s" % ''.join(map(chr, data))
	                data = []
	                swiped = False
	                continue
	            else:
	                break   # we got it!
	    #timeout
	    if time.time() > future:
	    	label.set("Swipe timed out. Click swipe in again.")
	    	return -1

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

	UIN = info['account_number'][4:-3]
	label.set("Successful swipe.")
	return UIN

def main():
	root = Tk()
	root.geometry("500x300+300+300")
	
	#Change for production
	domainAndPort = 'localhost:8000'

	app = TattGui(root, domainAndPort)
	root.mainloop()

if __name__ == '__main__':
	main()