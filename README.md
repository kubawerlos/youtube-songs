# YouTube songs

An action that will make it easier to generate and update YouTube song lists based on a simple configuration file.

You can see how this action is used [here](https://github.com/orgs/youtube-songs/repositories).

### Usage
Create a file `.github/youtube-songs.yaml` in your repository with the songs defined, as in the example below.
```yaml
country: 'PL' # ISO 3166-1 alpha-2 code of a country that will be used to check for songs availability
title: 'My favourite songs'

'Ballads':
    "Imagine (by John Lennon)":
        id: VOgFZfRVaww # YouTube ID
    'Bridge Over Troubled Water (by Simon & Garfunkel)':
        id: nvF5imxSaLI
    'Let It Be (by Beatles)':
        id: CTcb_33-DiI

'Rock':
    "I Love Rock 'N Roll (by Joan Jett and the Blackhearts)":
        id: d9jhDwxt22Y
    'Under Pressure (by Queen & David Bowie)':
        id: a9OPA-h8mAs
        source: Remastered 2011
    'By The Way (by Red Hot Chili Peppers)':
        id: Gtmnt-Ol1UY
        live: Slane Castle
    'Smooth Criminal (by Alien Ant Farm)':
        id: mjXRapctp6k
        cover: Michael Jackson

```

Create a GitHub Actions workflow (e.g. in `.github/workflows/update.yaml`) to update the lists, as in the example below.
```yaml
on:
  push: ~

jobs:
  update:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: kubawerlos/youtube-songs@v2
      - uses: stefanzweifel/git-auto-commit-action@v5

```

The above will create a `README.md` file like this:
```markdown
# My favourite songs

### [Ballads](https://www.youtube.com/watch_videos?title=Ballads&video_ids=VOgFZfRVaww,nvF5imxSaLI,CTcb_33-DiI)
1. :cd: "[Imagine (by John Lennon)](https://www.youtube.com/watch?v=VOgFZfRVaww)"
1. :cd: "[Bridge Over Troubled Water (by Simon & Garfunkel)](https://www.youtube.com/watch?v=nvF5imxSaLI)"
1. :cd: "[Let It Be (by Beatles)](https://www.youtube.com/watch?v=CTcb_33-DiI)"

### [Rock](https://www.youtube.com/watch_videos?title=Rock&video_ids=d9jhDwxt22Y,a9OPA-h8mAs,Gtmnt-Ol1UY,mjXRapctp6k)
1. :cd: "[I Love Rock 'N Roll (by Joan Jett and the Blackhearts)](https://www.youtube.com/watch?v=d9jhDwxt22Y)"
1. :cd: "[Under Pressure (by Queen & David Bowie)](https://www.youtube.com/watch?v=a9OPA-h8mAs)" (from Remastered 2011)
1. :fire: "[By The Way (by Red Hot Chili Peppers)](https://www.youtube.com/watch?v=Gtmnt-Ol1UY)" (live from Slane Castle)
1. :cd: "[Smooth Criminal (by Alien Ant Farm)](https://www.youtube.com/watch?v=mjXRapctp6k)" (Michael Jackson cover)

```
and update it whenever there is a change in the `.github/youtube-songs.yaml` file.


### Checking songs using Google API

To do this, add `GOOGLE_API_KEY` to your GitHub Actions workflow:
```yaml
      - uses: kubawerlos/youtube-songs@v2
        env:
          GOOGLE_API_KEY: ${{ secrets.GOOGLE_API_KEY }}
```

To perform the check regularly, set it as a scheduled task (in the example it will run once a day):
```yaml
on:
  push: ~
  schedule:
    - cron: '0 0 * * *'
```

Then, if a song is not available, information about it will be added to `README.md`:
```markdown
:exclamation: Incorrect songs: "Unavailable Song 1", "Unavailable Song 2" :exclamation:
```
