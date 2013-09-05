#!/usr/bin/env python2.6

import serial
import psycopg2
import datetime

class Teleinfo:

	ser = serial.Serial()
	
	def __init__ (self, port='/dev/teleinfo'):
		self.ser = serial.Serial(port, baudrate=1200, bytesize=serial.SEVENBITS, parity=serial.PARITY_EVEN)
	
	def checksum (self, etiquette, valeur):
		sum = 32
		for c in etiquette: sum = sum + ord(c)
		for c in valeur: 	sum = sum + ord(c)
		sum = (sum & 63) + 32
		return chr(sum)
		
	def read (self):
		# Attendre le debut du message
		while self.ser.read(1) != chr(2): pass
		
		message = ""
		fin = False
		
		while not fin:
			char = self.ser.read(1)
			if char != chr(3):
				message = message + char
			else:
				fin = True
		
		trames = [
			trame.split(" ")
			for trame in message.strip("\r\n\x03").split("\r\n")
			]
			
		tramesValides = dict([
			[trame[0],trame[1]]
			for trame in trames
			if ( (len(trame) == 3) and (self.checksum(trame[0],trame[1]) == trame[2])
			   or(len(trame) == 4) and (self.checksum(trame[0],trame[1]) == ' ') )
			])
			
		return tramesValides

if __name__ == "__main__":
	ti = Teleinfo()
	db = psycopg2.connect("host=localhost dbname=homedata user=supervision")
	while True:
		data = ti.read()
		dbc = db.cursor()
		dbc.execute( "INSERT INTO teleinfo (isousc,hchc,hchp,ptec,iinst,imax,papp, date)\
			VALUES (%s,%s,%s,%s,%s,%s,%s,%s);",
			  (data["ISOUSC"], data["HCHC"], data["HCHP"], data["PTEC"][0:2],
			   data["IINST"], data["IMAX"], data["PAPP"],
			   datetime.datetime.now() ) )
		dbc.execute( "COMMIT;" );
		dbc.close()
		text_file = open("/tmp/papp", "w")
		text_file.write("%s"%data["PAPP"])
		text_file.close()
