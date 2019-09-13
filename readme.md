<h1 align="center">AssetPond Craft CMS 3 Plugin</h1>

<h4 align="center">[FilePond](https://pqina.nl/filepond/) server for Craft Assets, based on [pqina/filepond-server-php](https://github.com/pqina/filepond-server-php).</h4>

<p align="center"><a href="https://scrutinizer-ci.com/g/workingconcept/assetpond-craft-plugin/"><img src="https://scrutinizer-ci.com/g/workingconcept/assetpond-craft-plugin/badges/quality-score.png?b=master" alt="Scrutinizer status"></a></p>

**This project is in early development. Use at your own risk!**

## How to Use It

- Add it to your project with with `composer require workingconcept/asset-pond`, then install from the control panel GUI or `craft install/plugin asset-pond`.
- Choose a default Volume for uploads in the plugin Settings.
- Use FilePond on your frontend however you fancy (React, Vue, Angular, or jQuery), using `filepond` for the field name and `{{ craft.assetpond.endpoint }}` for FilePond's `server` setting.
- Optionally provide a different target Volume ID. If your Volume ID is `7`, for example, it'd look like `{{ craft.assetpond.endpoint(7) }}`.

## TODO

- [ ] Enforce upload permissions and use CSRF.
- [ ] Support a field name other than just `filepond`, since that's a little silly.
- [ ] Allow for an array of field names.
- [ ] Finish and test Server.
    -  [ ] handleFetchRemoteFile()
    -  [ ] handleFilePost()
    -  [ ] handleTransferIdsPost()
- [ ] Support variants for smooth integration with [Doka](https://pqina.nl/doka) or `FilePondPluginImageTransform`.
- [ ] Add civilized documentation.