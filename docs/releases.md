---
layout: default
title: Releases and Changelog
redirect_from:
    - /changelog/
    - /upgrading/
    - /upgrading/changelog/
---

# Releases

These are the release notes from `Uri`. We’ve tried to cover all changes, including backward compatible breaks from 4.0 through to the current stable release. If we’ve missed anything, feel free to create an issue, or send a pull request.

{% for release in site.github.releases %}
## [{{ release.name }}]({{ release.html_url }}) - {{ release.published_at | date: "%Y-%m-%d" }}
{{ release.body | markdownify }}
{% endfor %}