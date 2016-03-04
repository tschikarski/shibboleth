.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt


.. _administration-faq-1-how-to-prepare-the-server-for:

How to prepare the server for multiple subdomains
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You may want to use more than one subdomain of the same second level domain for your website. 

- Choose a subdomain to be used for the Shibboleth authentication process. You may use the same
  subdomain for one of your websites. However, this is not necessary.
- Use absolute sessions_handlerURL, for example: https://shibboleth.yourdomain.tld/Shibboleth.sso.
  Use this domain for registration at the IdP. **Remark: Probably, you need a valid SSL certificate
  only for this (sub-)domain, as far as the Shibboleth authentication process is concerned.** 
- In shibboleth2.xml tag Sessions with cookieProps="; domain=yourdomain.tld; path=/"; you may add
  “; secure” to the cookieProps string. However, in that case, all of your protected websites
  must be https.
- In shibboleth2.xml, section RequestMappers (if present) take care to select all hosts that are in
  the scope of the websites, you want to authenticate for. Example:

::

    <HostRegex regex="/.yourdomain.tld/">
    <Path name="" authType="shibboleth" requireSession="false"/>
    </HostRegex>

