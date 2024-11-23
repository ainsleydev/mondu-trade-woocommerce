#!/bin/bash
#
# seed.sh
#
# Seeds or deletes random users for testing with the
# Mondu Trade API.

# User data: Use plain strings with a delimiter instead of associative arrays
users=("accepted:accepted.good.ainsley-dev"
       "pending:pending.pending-brc.ainsley-dev"
       "declined:declined.bad.ainsley-dev")

# Function to generate a random 16-digit number
generate_random_number() {
  echo $((RANDOM * RANDOM * RANDOM % 10000000000000000))
}

# Function to seed users
seed_users() {
  echo "Seeding users..."

  for user in "${users[@]}"; do
    # Split the user data into parts
    username=$(echo "$user" | cut -d':' -f1)
    email_base=$(echo "$user" | cut -d':' -f2)

    # Generate random email
    random_number=$(generate_random_number)
    email="$email_base-$random_number@example.com"

    # Capitalize first letter of the username for last name
    last_name="$(echo "${username:0:1}" | tr '[:lower:]' '[:upper:]')${username:1}"

    # Create user
    wp user create "$username" "$email" \
      --user_pass="password" \
      --role="administrator" \
      --first_name="Mondu" \
      --last_name="$last_name"

	# Add WooCommerce billing details
	wp user meta update "$user_id" billing_first_name "Mondu"
	wp user meta update "$user_id" billing_last_name "$last_name"
	wp user meta update "$user_id" billing_address_1 "1 Town Lane"
	wp user meta update "$user_id" billing_city "London"
	wp user meta update "$user_id" billing_postcode "NW1 9TY"
	wp user meta update "$user_id" billing_country "GB"
	wp user meta update "$user_id" billing_phone "07830465221"

    echo "Created user: $username, Email: $email"
  done
}

# Function to delete users
delete_users() {
  echo "Deleting users..."

  # Get all users with their emails
  user_list=$(wp user list --fields=ID,user_email --format=csv)

  # Iterate through the user list
  while IFS=',' read -r user_id user_email; do
    # Skip the header row
    if [ "$user_id" == "ID" ]; then
      continue
    fi

    # Check if the user email starts with any of the prefixes
    for user in "${users[@]}"; do
      prefix=$(echo "$user" | cut -d':' -f2)
      if [[ "$user_email" == "$prefix"* ]]; then
        # Delete user
        wp user delete "$user_id" --yes
        echo "Deleted user ID: $user_id, Email: $user_email"
      fi
    done
  done <<< "$user_list"
}

# Main script
case $1 in
  seed)
    seed_users
    ;;
  delete)
    delete_users
    ;;
  *)
    echo "Usage: $0 {seed|delete}"
    exit 1
    ;;
esac
