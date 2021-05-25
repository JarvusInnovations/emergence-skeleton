# Content Editor webapp

## Code layout

- `sencha-workspace/`
    - `packages/`
        - `emergence-cms/`: Primary location for content editor UI code
        - `emr-skeleton-theme/`: The Sencha theme used when rendering the content editor
    - `EmergenceContentEditor/`: A thin Sencha application used to make development easier and to provide a build target for generating the theme
- `html-templates/`
    - `webapps/EmergenceContentEditor/sencha.tpl`: A template for rendering the content editor embedded in the site's design frame
    - `html-templates/blog/blogPostEdit.tpl`: A wrapper around `sencha.tpl` to provide the content editor UI on the blog post edit form
    - `html-templates/pages/pageEdit.tpl`: A wrapper around `sencha.tpl` to provide the content editor UI on the page edit form

## Running live changes

The frontend Sencha application needs to be built at least once with the Sencha CMD build tool to scaffold/update a set of loader files. After that, you can just edit files the working tree and reload the browser. The two exceptions where you need to build again are changing the list of packages or changing the list of override files.

Before the frontend application can be built to run from live changes, you'll need to ensure all submodules are initialized:

```bash
git submodule update --init
```

Then, use the shortcut studio command for building the frontend application:

```bash
build-content-editor
```

Once built, the live-editable version of the app can be accessed via the static web server that the studio runs on port `{{ studio.static_port }}`. The backend host must be provided to the apps via the `?apiHost` query parameter. Any remote backend with CORS enabled will work, or you can use the local backend:

[`localhost:{{ studio.static_port }}/EmergenceContentEditor/?apiHost=localhost:{{ studio.web_port }}`](http://localhost:{{ studio.static_port }}/EmergenceContentEditor/?apiHost=localhost:{{ studio.web_port }})

## Working with breakpoints

By default, the Sencha framework will automatically append random cache buster values to every loaded `.js` source. This helps ensures that your latest code always runs, but will also prevent any breakpoints you set from persisting across reloads.

With the **Disable cache** option of the network inspector activated, you can disable this built-in cache buster by appending `&cache=1` to the current page's query string.

## Connecting to remote server

You can connect to any remote instance that has CORS enabled by appending the query parameter `apiHost` when loading the page. If the remote instance requires HTTPS, append `apiSSL=1` as well.
