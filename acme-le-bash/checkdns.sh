#!/bin/bash

D=$DOMAIN
while echo "$D"|grep -q '\.'
do
  dig "$D" soa |grep "^$D\.[[:space:]]" |grep -F "$D." |grep -q '[[:space:]]SOA[[:space:]]' && break
  D=`echo "$D"|sed -e 's/^[a-z0-9-]*\.//'`
done
ZONE_APEX=`echo "$D"|grep '\.'`

TOKEN=`curl -sX PUT http://169.254.169.254/latest/api/token -H "X-aws-ec2-metadata-token-ttl-seconds: 21600"`
IP=`curl -s -H "X-aws-ec2-metadata-token: $TOKEN" http://169.254.169.254/latest/meta-data/public-ipv4`
DNSIP=`dig $DNSMANE +short`

if [ "$IP" -a "$IP" != "$DNSIP" ]
then
  HOSTED_ZONE_ID=`aws --output json route53 list-hosted-zones-by-name --dns-name "$ZONE_APEX" | jq -r '.HostedZones[0].Id' |sed -e 's#^/hostedzone/\([A-Z0-9]*\).*$#\1#' | grep '^[A-Z0-9]\{1,\}$'`
  BATCH='{"Changes": [{ "Action": "UPSERT", "ResourceRecordSet": {"Name": "'$DOMAIN'", "Type": "A", "TTL": 300, "ResourceRecords": [{"Value":"'$IP'"}]}}]}'
  aws route53 change-resource-record-sets --hosted-zone-id "$HOSTED_ZONE_ID" --change-batch $BATCH
fi
