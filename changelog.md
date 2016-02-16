---
layout: default
title: Version 4 - Changelog
---

# Changelog

All Notable changes to `Uri` will be documented in this file

{% for release in site.github.releases %}
## {{ release.name }}
{{ release.body | replace:'```':'~~~' | markdownify }}
{% endfor %}