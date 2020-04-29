Plugin for accessing information on Fitbit devices
(number of steps, calories, distance…)

Setup 
=============

Once the Fitbit plugin is installed, go to :

![fitbit 1](../images/fitbit-1.JPG)

Equipment creation and association with Fitbit account 
--------------------------------------------------------

> **Tip**
>
> As in many places on Jeedom, put the mouse on the far left
> brings up a quick access menu (you can
> from your profiles always leave it visible)

Click on add a person to add someone :

![fitbit 2](../images/fitbit-2.JPG)

Give a name to this person (this equipment) and validate :

![fitbit 3](../images/fitbit-3.JPG)

You should then have this page :

![fitbit 4](../images/fitbit-4.JPG)

> **Important**
>
> The first thing to check is "Return URL". This must be
> validated and accessible from the outside otherwise you will not be able to
> link Jeedom to your Fitbit account. If not, put
> update your network configuration settings in General →
> Administration → Setup then "networks" part, see
> [here](https://www.jeedom.fr/doc/documentation/core/fr_FR/doc-core-administration.html.html#administration)

> **Note**
>
> It is recommended when connecting between Jeedom and Fitbit to be connected
> to Jeedom by the external ip

To link your Fitbit account to Jeedom it you first create
a Fitbit developer account by going
[here](https://dev.fitbit.com/fr), then you need to log in
then do : "Register an app". Then fill in the information
:

-   Application Name : Jeedom

-   Description : Jeedom

-   Application Website : <https://jeedom.com>

-   Organization : Jeedom

-   Organization Website : <https://jeedom.com>

-   OAuth 2.0 Application Type : Server

-   Callback URL : You have to put the one that Jeedom gives you during the
    creation of Fitbit equipment, be careful if you do not
    good the connection cannot be made

-   Default Access Type : Read & Write

You can also add a subscription, this allows Fitbit to
push an update directly into Jeedom (instead of waiting for
Jeedom update information) :

-   Default : Yes

-   Endpoint URL : the one given by Jeedom

-   Type : JSON body

-   Subscriber ID : Put nothing

If you have a subscription you must (be careful to do all
these steps in order) :

-   Put the subscription number in the Fitbit equipment on Jeedom

-   Put the subscription code in the Fitbit equipment on Jeedom

-   Back up equipment

-   On the Fitbit site launch the verification of the subscription

-   Then "Link to a user" the Jeedom equipment, as below

> **Important**
>
> Currently Fitbit supports https badly, so if you are in https
> there is little chance that it will work

Click "Link to User" to link this device to your
Fitbit account :

![fitbit 5](../images/fitbit-5.JPG)

Enter your Fitbit account credentials, then validate the
authorization request :

If you get a blank page or an error, your
network configuration is not good, otherwise you must fall back on
this page :

![fitbit 6](../images/fitbit-6.JPG)

For the subscription you need

Setup 
-------------

Here are the details of the plugin configuration :

![fitbit 6](../images/fitbit-6.JPG)

-   Name of the person : name of the Fitbit equipment

-   Parent object : name of the object to attach the equipment to

-   Activate / Visible : activates the equipment (do not forget to
    do it otherwise you will have no data) and make it visible
    on Dashboard

-   For each order :

    -   Historize : allows to log the command

    -   Show : allows to make it visible or not on the dashboard

    -   Advanced (small notched wheels) : displays the
        advanced control configuration

    -   Test : allows to test the command to see its value

    -   Delete ("-" button) : to delete the order

Widget 
------

Here is the fitbit widget :

![fitbit 7](../images/fitbit-7.JPG)
