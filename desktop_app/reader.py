import sys
import usb.core
import usb.util

import codes

VENDOR_ID = 0x0acd
PRODUCT_ID = 0x0520
DATA_SIZE = 1712 # I found this value programatically, it may be incorrect

def parse(entry):
    string = str(codes.usb_codes[entry])
    return string

device = usb.core.find(idVendor=VENDOR_ID, idProduct=PRODUCT_ID)

if device is None:
    sys.exit("Could not find usb reader")

if device.is_kernel_driver_active(0):
    try:
        device.detach_kernel_driver(0)
    except usb.core.USBError as e:
        sys.exit("Could not detatch kernel driver: %s" % str(e))

try:
    device.set_configuration()
    device.reset()
except Exception,e:
    sys.exit("Could not set configuration: %s" % str(e))

endpoint = device[0][(0,0)][0]

data = []
swiped = False
while 1:
    if len(data) == DATA_SIZE/8:
        data = map(parse, data)
        string = ""
        for item in data:
            string += item
        print string
        data = []
    try:
        tmpdata = device.read(endpoint.bEndpointAddress, endpoint.wMaxPacketSize)
        tmpdata = map(hex,tmpdata)
        tmpstr = ["[" + tmpdata[0] + ", " + tmpdata[2] + "]"]
        data += tmpstr

        if not swiped:
            print "Reading..."
        swiped = True
    except usb.core.USBError as e:
        if e.args == ('Operation timed out',) and swiped:
            print 'timeout'
            if len(data) < DATA_SIZE:
                print "Bad swipe, try again. (%d bytes)" % len(data)
                print "Data: %s" % ''.join(map(chr, data))
                data = []
                swiped = False
                continue
            else:
                break   # we got it!
