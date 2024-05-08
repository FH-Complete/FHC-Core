#!/bin/sh
cd chrome
rm fhcomplete.jar
zip -r fhcomplete.jar content skin
cd ..
rm fhcomplete.xpi
zip -r fhcomplete.xpi chrome defaults chrome.manifest install.rdf
