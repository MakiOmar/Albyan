<?xml version="1.0" encoding="UTF-8"?>
<!-- Human-readable view of a urlset sitemap in the browser; crawlers use the raw XML. -->
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:s="http://www.sitemaps.org/schemas/sitemap/0.9"
    exclude-result-prefixes="s">

    <xsl:output method="html" encoding="UTF-8" indent="yes" doctype-system="about:legacy-compat"/>

    <xsl:template match="/">
        <html lang="en">
            <head>
                <meta charset="utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1"/>
                <title>XML Sitemap</title>
                <style type="text/css">
                    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; margin: 2rem; color: #1e1e1e; line-height: 1.5; }
                    h1 { font-size: 1.75rem; margin-bottom: 0.5rem; }
                    p.intro { color: #444; max-width: 48rem; margin-bottom: 1.5rem; }
                    p.intro a { color: #0969da; }
                    table { border-collapse: collapse; width: 100%; max-width: 64rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08); font-size: 0.9rem; }
                    th, td { text-align: left; padding: 0.5rem 0.75rem; border: 1px solid #ddd; vertical-align: top; }
                    th { background: #f6f8fa; font-weight: 600; }
                    tr:nth-child(even) { background: #fafafa; }
                    tr:hover { background: #f0f7ff; }
                    td a { color: #0969da; text-decoration: none; word-break: break-all; }
                    td a:hover { text-decoration: underline; }
                    .muted { color: #666; font-size: 0.95rem; margin-top: 2rem; }
                </style>
            </head>
            <body>
                <h1>XML Sitemap</h1>
                <p class="intro">
                    This is an XML Sitemap file (URL list) for search engines.
                    More information: <a href="https://www.sitemaps.org/">sitemaps.org</a>.
                </p>
                <p class="intro">
                    This file contains <strong><xsl:value-of select="count(s:urlset/s:url)"/></strong> URLs.
                </p>
                <table>
                    <thead>
                        <tr>
                            <th>URL</th>
                            <th>Last Modified</th>
                            <th>Priority</th>
                            <th>Change frequency</th>
                        </tr>
                    </thead>
                    <tbody>
                        <xsl:for-each select="s:urlset/s:url">
                            <tr>
                                <td><a href="{s:loc}"><xsl:value-of select="s:loc"/></a></td>
                                <td>
                                    <xsl:choose>
                                        <xsl:when test="s:lastmod"><xsl:value-of select="s:lastmod"/></xsl:when>
                                        <xsl:otherwise>—</xsl:otherwise>
                                    </xsl:choose>
                                </td>
                                <td>
                                    <xsl:choose>
                                        <xsl:when test="s:priority"><xsl:value-of select="s:priority"/></xsl:when>
                                        <xsl:otherwise>—</xsl:otherwise>
                                    </xsl:choose>
                                </td>
                                <td>
                                    <xsl:choose>
                                        <xsl:when test="s:changefreq"><xsl:value-of select="s:changefreq"/></xsl:when>
                                        <xsl:otherwise>—</xsl:otherwise>
                                    </xsl:choose>
                                </td>
                            </tr>
                        </xsl:for-each>
                    </tbody>
                </table>
                <p class="muted">Styled for readability in the browser only. Search engines read the underlying XML.</p>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
