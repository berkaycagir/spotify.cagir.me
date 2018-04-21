#!/usr/bin/python
import sys, checker_library

recipient = str(sys.argv[1])
input_list = checker_library.load_list()

if(len(sys.argv) == 3):
    song_id = str(sys.argv[2])
    i = next((i for i, v in enumerate(input_list) if v[0] == song_id and v[2] == recipient), None)
    if i != None:
        del input_list[i]
        checker_library.send_mail('The checker with ID %s and mail addresss %s has been successfully deleted from https://spotify.cagir.me database.' % (song_id, recipient), 'Your e-mail address has been deleted', recipient)
        checker_library.save_list(input_list)
        sys.exit(0)
    sys.exit(1)
else:
    indices = [i for i, v in enumerate(input_list) if v[2] == recipient]
    if(indices):
        checker_library.send_mail('Your e-mail address has been successfully deleted from https://spotify.cagir.me database.', 'Your e-mail address has been deleted', recipient)
        checker_library.save_list([v for i, v in enumerate(input_list) if i not in indices])
        sys.exit(0)
    sys.exit(1)
