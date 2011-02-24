#!/usr/bin/python

NOTES_DIRECTORY = '/home/dylan/docs/notes'

import os
import time
import MySQLdb as db
import ConfigParser

config = ConfigParser.RawConfigParser()
config.read('/etc/dylanstestserver.ini')
domain = config.get('database', 'domain')
user = config.get('database', 'user')
password = config.get('database', 'password').replace('"', '')
database = config.get('database', 'database')
cursor = db.connect(domain, user, password, database).cursor()

notes = os.listdir(NOTES_DIRECTORY)

sql = "SELECT title FROM notes"
cursor.execute(sql)
results = cursor.fetchall()
existing_titles = []
for row in results:
    existing_titles.append(row[0])

for note in notes:
    if note == 'index.php' or note == 'notes.php': continue
    url = note[:note.index('.')]
    f = open(os.path.join(NOTES_DIRECTORY, note))
    title = str(f.readline()[:-1])
    text = ''.join(f.readlines()) #converts list to single string
    if title in existing_titles: continue
    mtime = time.localtime(os.path.getmtime(os.path.join(NOTES_DIRECTORY, note)))
    date_posted = "%s-%s-%s" % (str(mtime.tm_year)[2:], mtime.tm_mon, mtime.tm_mday)
    sql = "INSERT INTO notes (date_posted, url, title, text)\
             VALUES(\"%s\", \"%s\", \"%s\", \"%s\")"\
             % (date_posted, url, title, db.escape_string(text))

    #print sql
    cursor.execute(sql)
