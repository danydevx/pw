# Password Generator

Adds a password generator to InputfieldPassword.

![pg](https://github.com/user-attachments/assets/05e602a4-8364-4ab8-bb84-81a7f7139369)

## Usage

[Install](http://modules.processwire.com/install-uninstall/) the Password Generator module.

Now any InputfieldPassword has a password generation feature. The settings for the generator are taken automatically from the settings* of the password field, or you can configure the generator settings in the module config.

*Settings not supported by the generator:

* Complexify: but generated passwords should still satisfy complexify settings in the recommended range.
* Banned words: but the generated passwords are random strings so actual words are unlikely to occur.
