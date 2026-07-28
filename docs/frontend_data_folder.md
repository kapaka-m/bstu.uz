# Frontend Data Folder

`apps/web/src/data` is not a production content source.

There are no runtime data files in that folder now. Public website, academic,
media, menu, page, translation, branding, and mobile-visible content must come
from the Laravel API/MySQL database and be managed through `/apanel/`.

Legacy React content captures were moved into API seed JSON. The old
`scripts/import-react-content` helper was removed after migration review.

The reviewed source-of-truth data lives in `apps/api/database/data`.
