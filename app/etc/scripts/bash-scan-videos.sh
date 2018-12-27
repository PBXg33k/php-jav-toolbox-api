#!/usr/bin/env bash
PROCESSED=() # Create empty array so code doesn't fail

echo "Checking for previous run"
if [[ -f ./video-check-processed.list.txt ]]; then
    echo "Loading processed list"
    readarray PROCESSED < ./video-check-processed.list.txt
    echo "Loaded ${#PROCESSED[@]} items"
fi

echo "Scanning video files"
find $1 -type f -exec file -N -i -- {} + | sed -n 's!: video/[^:]*$!!p' | sed 's/.*\.\/\(.*\)/\1/g' | while IFS= read -r FILE; do

    if [[ ${PROCESSED[*]} =~ "./$FILE" ]]; then
        echo "Already processed $FILE"
        continue
    fi
    echo "Processing $FILE"

    php /var/www/app/bin/console jav:process-file -f "$FILE" -vvv
    #docker run --rm -v $PWD:/tmp/ jrottenberg/ffmpeg:3.4-alpine -v error -err_detect explode -xerror -i "$FILE" -map 0:1 -f null -

    if [[ $? -eq 0 ]]; then
        # Much success wow
        echo "Check succeeded $FILE"
        echo "./$FILE" >> ./video-check-success.list.txt
    else
        echo "Check failed $FILE"
        # error, append filename to error file.
        echo "./$FILE" >> ./video-check-fail.list.txt
    fi

    echo "./$FILE" >> ./video-check-processed.list.txt
done