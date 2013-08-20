#!/usr/bin/python2.7
import time
import serial
#import subprocess 
from time import sleep
import datetime
import sys

def checksum (etiquette, valeur):
                sum = 32
                for c in etiquette: sum = sum + ord(c)
                for c in valeur:        sum = sum + ord(c)
                sum = (sum & 63) + 32
                return chr(sum)

#=========================================================================
# Fonction LireTeleinfo
#=========================================================================
def LireTeleinfo ():
                # Attendre le debut du message
                while ser.read(1) != chr(2): pass

                message = ""
                fin = False
		while not fin:
                       char = ser.read(1)
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
                       if ( (len(trame) == 3) and (checksum(trame[0],trame[1]) == trame[2])
                          or(len(trame) == 4) and (checksum(trame[0],trame[1]) == ' ') )
                       ])
               	return tramesValides
                

#=========================================================================
# Connexion au port
#=========================================================================
ser = serial.Serial(
	port='/dev/ttyAMA0',
	baudrate=1200,
	parity=serial.PARITY_EVEN,
	stopbits=serial.STOPBITS_ONE,
	bytesize=serial.SEVENBITS )

#=========================================================================
# Traitement Premiere voie RPIDOM
#=========================================================================
ser.write('A')
sleep(1)
var = 1
vNbRetry = 0
vlogdir ="/var/log/teleinfo/"
vPreviousSave=""
vPreviousPAPP=""
vPreviousPTEC=""
while True:
	ser.flushInput()
	data = LireTeleinfo()

#=========================================================================
# Definition des des variables temporelles
#=========================================================================
	vHEURE = datetime.datetime.now().strftime('%H:%M:%S')
	vDATE = datetime.datetime.today().strftime('%Y-%m-%d')
	vCURRENTHOUR = datetime.datetime.now().strftime('%H')
#=========================================================================
	#print ( "HCHC ="+ data["HCHC"],"HCHP= " +  data["HCHP"], "PTEC = "+data["PTEC"][0:2],"IINST= " +  "PAPP="+ data["PAPP"], vDATE, vHEURE)

	try:
		if (vPreviousSave!=vCURRENTHOUR) :
			with open(vlogdir+"HCHC-HCHP-"+vDATE+".csv", "a") as hchpfile:	
				hchpfile.write(vDATE +" "+ vHEURE+","+ data["HCHC"]+","+ data["HCHP"]+"\n")
			vPreviousSave = vCURRENTHOUR
			hchpfile.close()
		if(vPreviousPAPP != data["PAPP"] or vPreviousPTEC != data["PTEC"][0:2]) :
			vPreviousPAPP = data["PAPP"]
			vPreviousPTEC = data["PTEC"][0:2]
			with open(vlogdir+"PAPP-"+vDATE+".csv", "a") as myfile:
	 			myfile.write(vDATE +" "+ vHEURE+"," +data["PAPP"]+","+ data["PTEC"][0:2] + "\n")
			myfile.close()
		vNbRetry = 0
	except : 
		#print ("On attend 3 s avant de recommencer")
		sleep (3)
		vNbRetry += 1
		if (vNbRetry < 10) :
			pass
		else : 
			with open(vlogdir+"error.log", "a") as errorfile:
				errorfile.write(vDATE+" " + vHEURE + ": Y a un prob on arrete la : "+ str(sys.exc_info()[0]) +"\r\n")
			sys.exit()	

ser.close()
