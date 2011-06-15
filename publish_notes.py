#!/usr/bin/python

NOTES_DIRECTORY = '/home/dylan/docs/notes'

import os
import time
import MySQLdb as db
import ConfigParser

config = ConfigParser.RawConfigParser()
config.read('/etc/dylansserver.ini')
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
    if note[:1] == '.': continue
    if note == 'index.php' or note == 'notes.php': continue
    url = note[:note.index('.')]
    f = open(os.path.join(NOTES_DIRECTORY, note))
    title = str(f.readline()[:-1])
    date_posted = time.strptime(str(f.readline()[:-1]), "%Y/%m/%d %I:%M%p")
    text = ''.join(f.readlines()) #converts list to single string
    if title in existing_titles: continue
    sql = "INSERT INTO notes (date_posted, url, title, text)\
             VALUES(\"%s\", \"%s\", \"%s\", \"%s\")"\
             % (time.strftime("%Y/%m/%d %I:%M:00", date_posted), url, title, db.escape_string(text))

    #print sql
    cursor.execute(sql)
