#!/bin/sh
set -eu

load_secret() {
    variable_name="$1"
    file_variable_name="${variable_name}_FILE"
    secret_file="$(eval "printf '%s' \"\${$file_variable_name:-}\"")"

    if [ -n "$secret_file" ] && [ -f "$secret_file" ]; then
        secret_value="$(cat "$secret_file")"
        export "$variable_name=$secret_value"
        unset "$file_variable_name"
    fi
}

load_secret APP_KEY
load_secret DB_PASSWORD

exec "$@"
