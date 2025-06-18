# advcallspy
Advanced Call Spy for FreePBX v17+ based systems

#### ALPHA RELEASE
Bugs and/or issues may be found. Please open an ticket in the issue tracker if found. PRs are also welcome for review.

### Overview
This module allows for more advanced control over the ChanSpy and ExtenSpy features from Asterisk available in FreePBX.

*Note:* This will disabled the default ChanSpy feature code (555) and replace it within the module. 

### Features
* Select between ChanSpy or ExtenSpy for spying.
* Multiple Spy feature codes can be created.
* Set what spy modes (Barge, Whisper, etc) can be used
* Restrict who can spy (Spiers)
* Restrict which extensions can be spied on.
* Create Spy Groups and add extensions to the group. Restrict spying to only extensions in the group.
* Generate CEL User Event for starting/stopping of spying
* Create BLF hints to monitor to know when a spy feature code is being used.

### Installation

* `fwconsole ma downloadinstall https://github.com/blazestudios97/advcallspy/releases/download/17.0.1.4/advcallspy-17.0.1.4.tar.gz`
* `fwconsole r`
