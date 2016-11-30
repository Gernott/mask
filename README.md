###Fork for Gernott/mask
[Github Repo](https://github.com/Gernott/mask)

###Request
Find a good solution for [Forge Ticket](https://forge.typo3.org/issues/75312) Its a big topic and i need feedback

###Changes
- All $extSettings paths filled by settings service. This service was extend by two new getters:
  - frontendService
  - backendService
- JSON path is further available in ExtConf 
- Default mask path is set outside from fileadmin. fileadmin should filled by generated content?
- HTML Files for new elements would be saved in every path entry

###Individual paths
You can control this paths with typoscript:

####Frontend
<pre>
plugin.tx_mask {
  settings {
    feview {
      templateRootPaths.1 = templates2/content/
      partialRootPaths.1 = templates2/content/Partials/
      layoutRootPaths.1 = templates2/content/Layout/
    }
  }
}
</pre>

####Backend
<pre>
module.tx_mask {
  settings {
    beview {
      templateRootPaths.1 = templates2/backend/
      partialRootPaths.1 = templates2/backend/Partials/
      layoutRootPaths.1 = templates2/backend/Layout/
      previewRootPaths.1 = templates2/preview/
      contentRootPaths.1 = templates2/content/      
    }
  }
}
</pre>