#!/usr/bin/python2.7
import time
from time import sleep
import datetime
import sys
import ow
#=========================================================================
# Definition des des variables temporelles
#=========================================================================
vHEURE = datetime.datetime.now().strftime('%H:%M:%S')
vDATE = datetime.datetime.today().strftime('%Y-%m-%d')
vCURRENTHOUR = datetime.datetime.now().strftime('%H')


def treewrite( sensor, logpath ):
   # print '%7s - %s' % ( sensor._type, sensor._path )
    for next in sensor.sensors( ):
        if next._type in [ 'DS2409', ]:
            tree( next )
        else:
  #          print '%7s - %s' % ( next._type, next._path)
  #          print (logpath + next.temperature)
	    with open(logpath+next._path+"-"+vDATE+".csv", "a") as tempfile:
            	tempfile.write(vDATE +" "+ vHEURE+","+next.temperature+"\n")
	    tempfile.close()


if __name__ == "__main__":
    if len( sys.argv ) == 1:
        print 'usage: tree.py u|serial_port_path|localhost:4304'
        sys.exit( 1 )
    else:
        ow.init( sys.argv[ 1 ] )
        treewrite( ow.Sensor( '/' ) ,'/var/log/1wire/temperature')
#with open(vlogdir+"HCHC-HCHP-"+vDATE+".csv", "a") as hchpfile:  
  #                               hchpfile.write("'"+vDATE +" "+ vHEURE+"';"+ data["HCHC"]+";"+ data["HCHP"]+"\r\n")
#=========================================================================
