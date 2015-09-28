import json
import urllib
import urllib2

class Package:
  def __init__(self, json):
    # TODO - Lire json
    self.version = ""
	self.name = ""
	self.description = ""	

# Constante pour un json vide
EMPTY_JSON = "{}"
# Constante pour la version actuelle
CURRENT_VER = "0.1.0"

def compose_url(pkg_name):
  # TODO - Déterminer la convention pour génération d'url
  pass

def exists(pkg_name):
  url = compose_url(pkg_name)
  content = urllib.urlopen(link)
  if Json.loads(content.read()) == EMPTY_JSON:
    print "Cannot find package " % pkg_name
	return False
  return True

def download_content(url, name):
  urllib.urlretrieve(url, name)

def read_json(json):
  pass
  
# Point d'entrée
if __name__ == '__main__':
  import sys
  if len(sys.argv) >= 2:
    if sys.argv[1] == "-v":
	  print "Current version is " % CURRENT_VER
    if len(sys.argv) == 3 and sys.argv[1] == "-i":
      if exists(sys.argv[2]):
	    # TODO - Nom à composer
	    name = ""
		try:
			download_content(compose_url(name), name)
		except IOError:
			print "Cannot find package " % name	
  else:
    print "No argument passed. Stopping."
