#!/usr/bin/python2.7
import time
import serial
#import subprocess 
#from time import sleep
import datetime

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
#sleep(1)
ser.flushInput()
data = LireTeleinfo()

#=========================================================================
# Definition des des variables temporelles
#=========================================================================
vHEURE = datetime.datetime.now().strftime('%H:%M')
vDATE = datetime.datetime.today().strftime('%Y-%m-%d')
#=========================================================================
#print ( "ISOUSC = "+ data["ISOUSC"],"HCHC ="+ data["HCHC"],"HCHP= " +  data["HCHP"], "PTEC = "+data["PTEC"][0:2],"IINST= " + data["IINST"], "IMAX= " + data["IMAX"], "PAPP="+ data["PAPP"], vDATE, vHEURE)

with open(vDATE+".csv", "a") as myfile:
  myfile.write(vDATE +" "+ vHEURE+","+ data["ISOUSC"] +","+ data["HCHC"]+","+ data["HCHP"]+"," +data["PTEC"][0:2]+","+data["IINST"]+","+ data["IMAX"]+","+ data["PAPP"] )

ser.close()
