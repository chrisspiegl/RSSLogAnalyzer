![current status](https://img.shields.io/badge/current%20status-halted-red?style=flat-square)

## Halted Project

I lost motivation to work on this for now. The code is here if you want to take a look.

## IMPORTANT

The old version is NOT compatible with the new version.

Documentation needs an update!

## README

This tool helps me keep track of my subscribers. It analyzes the apache log file and cumulates the subscribers. It includes Google Reader Subscribers, Other Agregators and Direct Subscribers.

I got inspired by Marco Arment because of his Github-Gist https://gist.github.com/3783146 and just improved upon it to count the RSS Subscribers for more than one domain. In addition I added an admin interface for easy accessibility of:

* Max Overall incl. Date
* Max last 7 Days
* Average Overall
* Average 7 Days

# Configuration

* copy ``config.example.php`` to ``config.php`` and add your data (you can use ``pwGen.php`` to generate the SHA1 password hash)
* copy ``example.htaccess`` to ``.htaccess`` to prevent the web to being able to see your log file etc.
* edit the variables in ``rssScript.bash``
* create a crown job to collect the rss data once per day (preferable short after midnight)

I guess you are done now. Hope it works. Please feel free to improve upon this.

# License

Have a look at the ``LICENSE``.
