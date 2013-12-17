#!/usr/bin/python

#  odi-multiselect.py - continuously read cards and post numbers to defined REST endpoint
# 
#  Adapted from multiselect.py, pacakaged as part of the RFIDiot library. (c) Adam Laurie
# 
#  This code is copyright (c) David Tarrant, 2013, All rights reserved.
#  The following terms apply:
#
#    This code is free software; you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation; either version 2 of the License, or
#    (at your option) any later version.
#
#    This code is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#

import rfidiot
import sys
import os
import time
import string
import httplib
import urllib
import subprocess
from subprocess import Popen
		
headers = {"Content-type": "application/x-www-form-urlencoded", "Accept": "text/plain"}

try:
        card= rfidiot.card
except:
        os._exit(True)

args= rfidiot.args

card.info('multiselect v0.1n')

# force card type if specified
if len(args) == 1:
        if not card.settagtype(args[0]):
		print 'Could not set tag type'
		os._exit(True)
else:
        card.settagtype(card.ALL)

while 42:
	if card.select():
		print '    Tag ID: ' + card.uid,
		params = urllib.urlencode({'action': "keycard", 'keycard_id': card.uid})
		conn = httplib.HTTPConnection("signin.office.theodi.org")
		conn.request("POST", "/server/cardProcessor.php", params, headers)
		response = conn.getresponse()
		print response.status, response.reason
		data = response.read()
		conn.close()	
		p = Popen(["afplay", str(response.status) + ".mp3"])
	else:
		print '    No card present\r',
		sys.stdout.flush()
