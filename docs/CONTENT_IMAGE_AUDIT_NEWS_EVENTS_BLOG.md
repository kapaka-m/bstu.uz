# Content Image Audit: News, Events, Blog

Date: 2026-07-18

## Scope

This audit checks the images used by the public content areas:

- News
- Events
- Blog

Checked database tables:

- `news`
- `news_translations`
- `blogs`
- `blog_translations`
- `media`

Checked storage folders:

- `apps/api/storage/app/public/news-events`
- `apps/api/storage/app/public/media/blog`

## Final Summary

- News and events records checked: 61
- Blog records checked: 8
- Missing image references: 0
- Duplicate image groups after fixes: 0
- Public page design changes: 0

The original issue was not missing files. The issue was semantic mismatch: several blog images were exact copies of unrelated news/event images, and one generic image was repeated by three different news/event records.

All identified mismatched/duplicated images were replaced with topic-specific images, and the old unreferenced duplicate files were removed from storage.

## Fixed Blog Images

These blog records were updated to unique topic-specific images.

| Record | New image | Previous issue |
| --- | --- | --- |
| `methods-of-combating-cybercrime-and-developing-cyber-hygiene` | `media/blog/content/cyber-hygiene.jpg` | Previously used Memory and Honor event photo. |
| `youth-initiatives-marathon-launches-in-bukhara` | `media/blog/content/youth-initiatives-marathon.jpg` | Previously used inclusive education/music therapy photo. |
| `phd-research-defense-personalized-education-for-gifted-students` | `media/blog/content/phd-research-defense.jpg` | Previously used leadership/youth meeting photo. |
| `iv-uzbekistan-turkey-rectors-forum-held-at-bstu` | `media/blog/content/uzbekistan-turkey-rectors-forum.jpg` | Previously used poetry awards photo. |
| `uzbekistan-turkey-rectors-forum-panels-and-key-outcomes` | `media/blog/content/rectors-forum-panels.jpg` | Previously used leadership/youth meeting photo. |
| `asian-womens-forum-bstu-showcases-ai-it-startups` | `media/blog/content/asian-womens-forum-ai-startups.jpg` | Previously used Hero of Uzbekistan photo. |
| `central-asian-spiritual-thought-and-islamic-philosophy-conference` | `media/blog/content/central-asian-philosophy-conference.jpg` | Previously used Engineers of the Future hall photo. |
| `cooperation-memorandum-signed-with-cci-of-kyrgyzstan` | `media/blog/content/kyrgyzstan-cci-memorandum.jpg` | Previously used textile/light industry event photo. |

## Fixed News/Event Images

These records previously shared the same exact image and were updated.

| Record | Category | New image |
| --- | --- | --- |
| `eco-plogging-is-an-important-step-towards-cleanliness-a-healthy-lifestyle-and-responsibility-to-future-generations` | Events | `news-events/eco-plogging-clean-healthy-lifestyle.jpg` |
| `resolution-of-the-cabinet-of-ministers-of-the-republic-of-uzbekistan` | News | `news-events/resolution-cabinet-ministers-uzbekistan.jpg` |
| `women-day-festive-event` | Events | `news-events/women-day-festive-event-2026.jpg` |

## Removed Duplicate Files

These old files are no longer referenced by `news`, `blogs`, or `media`, and were removed:

- `media/blog/blog-1.jpg`
- `media/blog/blog-2.jpg`
- `media/blog/blog-3.jpg`
- `media/blog/blog-4.jpg`
- `media/blog/blog-recent-1.jpg`
- `media/blog/blog-recent-2.jpg`
- `media/blog/blog-recent-3.jpg`
- `media/blog/blog-recent-4.jpg`
- `news-events/eco-plogging-is-an-important-step-towards-cleanliness-a-healthy-lifestyle-and-responsibility-to-future-generations.jpg`
- `news-events/resolution-of-the-cabinet-of-ministers-of-the-republic-of-uzbekistan.jpg`
- `news-events/women-day-festive-event.jpg`

## Audit Images

Contact sheets used for review are documentation-only files:

- `docs/image-audit-contact-sheets/blog.jpg`
- `docs/image-audit-contact-sheets/news_events.jpg`
- `docs/image-audit-contact-sheets/replacement_candidates.jpg`
- `docs/image-audit-contact-sheets/blog_after.jpg`
- `docs/image-audit-contact-sheets/news_events_after.jpg`

## Verification

Final database/storage verification:

- News/events records checked: 61
- Blog records checked: 8
- Missing image references: 0
- Duplicate image groups: 0

The source of truth for the public pages remains the database, served through the API. The public website, blog list, blog details, news list, news details, and homepage sections continue to read these image paths dynamically.
