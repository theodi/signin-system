Sign In System (v2)
===================

A web based sign in system for use in small offices.

Usage
-----

To deploy master to the live server:

    bundle
    cap deploy

This will automatically set up the database connection details, etc, and update the cached staff list from the content on the main website.

If you want to update the staff list manually without a code deploy, run:

    cap staff:update

License
-------

This code is open source under the MIT license. See the LICENSE.md file for 
full details.

Authors
-------

Dave Tarrant <davetaz@theodi.org>
James Smith <james.smith@theodi.org>
