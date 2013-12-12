Wichtlr
=====

Wichtlr is a CLI script that helps you organize a Secret Santa.

You know the problem: You and your social group are having a christmas party and you want to give away small gifts to each other. Of course you could draw notes from a hat to decide who gives a present to whom. But there are a few little problems: Usually there is at least one person drawing his own name. And even then it is not uncommon for someone to draw the name of his partner or a relative that they is going to give a gift anyway.

But you have to worry no more: Wichtlr can solve these problems.

**Wichtlr** is a program that determines a random solution to your problem and avoids anyone having to give a gift to themselves or their partner. When a solution is found, every participant is informed via mail. It is also completely anonymous: Not even *you* will be able to figure who was assigned to whom – unless, of course, you edit the script (which by the way you are very welcome to) or have control over the mail server.

Features
------------

 * **randomized solutions** (keep it for next year)
 * **anonymous**
 * **send mails** via PHP, sendmail or smtp
 * **preview mode** where all mails are sent to one address
 * **recovery data** if someone looses his mail
 * send **plain text** and **HTML mails**
 * **embed inline images** in your HTML mails (animated snowflakes FTW!)

Installation
----------------

    # Download composer
    curl -sS https://getcomposer.org/installer | php

    # Download the sources
    git clone https://github.com/czenker/wichtlr.git && cd wichtlr

    # Install dependencies
    php composer.phar install

    # Have fun**
    php bin/wichtlr.php go

HowTo
----------
### participants.yaml

The YAML file format uses spaces for indentation!

    # "jane" is an identifier. It has to be unique in the whole file
    jane:
        # the real name of the person – every participant should know who that is, so use nicknames if you like
        name: 'Jane Doe'
        # the address to send the information who Jane has to give a gift to
        email: 'jane@example.com'
        # an array of identifiers this user should not have to give something to
        not_to: ['john', 'alice']
    # ...

### mail.yaml

#### send via SMTP

The YAML file format uses spaces for indentation!

    swiftmailer:
        transport:  smtp
        encryption: ssl
        auth_mode:  login
        host:       smtp.gmail.com
        port:       ~
        username:   your_username
        password:   your_password

### default.twig

Wichtlr uses [Twig](http://twig.sensiolabs.org/) for templating.

Edit `conf/default.twig` to your needs. Make sure to remove none of the blocks. If you don't want to send an HTML mail, just empty the html block of any content.

#### Inline images (HTML Mail)

You can embed images into your HTML mail quite easily like so:

    <img src="{{ 'blinking_image.gif' | image }}" />
    <table width="600" background="{{ 'snow.gif' | image }}">
        <!-- ... -->
    </table>

The image files path is relative to the `conf/` folder.

#### Have different templates for different participants

    {% if donor.identifier == 'jane' %}
        Hi Sweety.
    {% elseif donor.identifier == 'john' %}
        Hey Dude.
    {% endif %}


### Dry run

You are asked if you want to do a dry-run by the script. If you do that, you can give your email address and all
the mails are appended to one mail and sent to you. This way you can test that your mail credentials work and your
mails look like they are supposed to.

### Recovery

If you have enabled recovery when sending the mails you might recover an association in case a mail got lost.

There are two other people who received a code for the person who lost their mail. It is a base64 encoded string.

Aquire those codes and run

    php bin/wichtlr.php recover

You are asked to enter those two codes and you should see the name that went missing.

Actually, *anyone* could run that recovery. There is no information stored on your machine that is required to recover the name. All you need is those two strings.