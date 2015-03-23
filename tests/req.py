import requests

r = requests.get('http://mqs.loc')
print 'Status', r.status_code
print r.headers
print r.encoding
print r.text
print r.json()
