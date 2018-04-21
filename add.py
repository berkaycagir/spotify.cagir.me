#!/usr/bin/python
import sys, checker_library

song_id = str(sys.argv[1])
country_id = str(sys.argv[2])
recipient = str(sys.argv[3])

input_list = checker_library.load_list()

if (song_id, country_id, recipient) not in input_list:
    input_list.append((song_id, country_id, recipient))
    checker_library.save_list(input_list)
    sys.exit(0)
else:
    sys.exit(1)
