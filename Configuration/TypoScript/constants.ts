
plugin.tx_shibboleth_loginlink {
    view {
        # cat=plugin.tx_shibboleth_loginlink/file; type=string; label=Path to template root (FE)
        templateRootPath = EXT:shibboleth/Resources/Private/Templates/
        # cat=plugin.tx_shibboleth_loginlink/file; type=string; label=Path to template partials (FE)
        partialRootPath = EXT:shibboleth/Resources/Private/Partials/
        # cat=plugin.tx_shibboleth_loginlink/file; type=string; label=Path to template layouts (FE)
        layoutRootPath = EXT:shibboleth/Resources/Private/Layouts/
    }
}
