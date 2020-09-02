*****************************************************
***  Module for our changes to the Group module   ***
*****************************************************

     *********   ******   ******     ******
     *********  ***  ***  *** ***   *** ***
        ***    ***        ***  *** ***  ***
        ***    ***        ***   *****   ***
        ***    ***        ***           ***
        ***     ***  ***  ***           ***
        ***      ******  ***             ***


*****************************************************
***  User friendly titles in add nodes            ***
*****************************************************

When the user tried to add a group node, the title is to complex and not user friendly. This overrides the rout with a more user friendly title.

The idea comes from; https://www.drupal.org/project/group/issues/2867202

If the patch in https://www.drupal.org/project/group/issues/2949408 is not in use, the code has to be modified and the if-statement in line 29 "if ($plugin_id !== 'group_membership') {}  " must be removed.


*****************************************************
***  Views default argument for curren user group ***
*****************************************************

Use the current users group permission as a contextual filter for groups.

The idea comes from; https://www.drupal.org/project/group/issues/2999661




*****************************************************
***  Views default argument Group Content ID      ***
*****************************************************

A Group Content ID views argument which allows for title replacements.

The idea comes from; https://www.drupal.org/project/group/issues/3152953





*****************************************************
*** Hide all the standard tabs on the node form   ***
*****************************************************

On all the node forms, the standard tabs are removed.


