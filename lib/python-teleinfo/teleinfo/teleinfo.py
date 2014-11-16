#!/usr/bin/env python

from .hw_vendors import HW_vendor
import itertools
import logging

logger = logging.getLogger(__name__)

class Teleinfo:
    MARKER_START_FRAME = chr(2)
    MARKER_STOP_FRAME = chr(3)
    MARKER_END_LINE = '\r\n'

    def __init__(self, hw=None):
        assert hw is not None and isinstance(hw, HW_vendor)
        self._hw = hw
        self._synchro_debut_trame()

    def get_frame(self):
        raw = self._get_raw_frame().strip(self.MARKER_END_LINE)
        groups = [line.split(" ", 2) for line in raw.split(self.MARKER_END_LINE)]
        frame = dict([
            (k, v) for k, v, chksum in groups if chksum == self._checksum(k, v)
        ])
        if len(frame) != len(groups):
            logger.info("Discarded fields because of bad checksum: {}".format(
                itertools.ifilterfalse(lambda g: g[2] != self._checksum(g[0], g[1]))
            ))
        return frame

    def _synchro_debut_trame(self):
        c = 0
        while self._hw.read_char() != self.MARKER_START_FRAME:
            c += 1
        logger.debug("Skipped {} bytes while synchronizing for start of frame".format(c))

    def _get_raw_frame(self):
        frame = ''.join(itertools.takewhile(
            lambda c: c != self.MARKER_STOP_FRAME,
            self._hw)
        )
        return frame

    def _checksum(self, key, value):
        chksum = 32
        chksum += sum([ord(c) for c in key])
        chksum += sum([ord(c) for c in value])
        chksum = (chksum & 63) + 32
        return chr(chksum)

def main(ps_name, argv):
    import getopt
    import json

    SUPPORTED_DEVICES = {
        "RpiDom": RpiDom,
        "SolarBox_USB": SolarBox_USB
    }

    def usage():
        print("{} -d|--device <hw_device>".format(ps_name))
        print("{} [-h|--help]".format(ps_name))
        print("")
        print("Supported hardware devices are:")
        print("  {}".format(SUPPORTED_DEVICES.keys()))

    device = None
    try:
        opts, args = getopt.getopt(argv, "hd:", ["help", "device="])
    except getopt.GetoptError:
        usage()
        sys.exit(2)
    for opt, arg in opts:
        if opt in ("-h", "--help"):
            usage()
            sys.exit()
        elif opt in ("-d", "--device"):
            try:
                device = SUPPORTED_DEVICES.get(arg)
            except KeyError:
                print("Invalid device: {}".format(arg))
                usage()
                sys.exit(2)
    if device is None:
      print("Missing device argument")
      usage()
      sys.exit(2)

    ti = Teleinfo(device())
    print(json.dumps(ti.get_frame(), indent=2, separators=(',', ':')))


if __name__ == "__main__":
    logging.basicConfig()
    import sys
    from .hw_vendors import *
    main(sys.argv[0], sys.argv[1:])

