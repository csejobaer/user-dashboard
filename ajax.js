$(document).ready(function() {
    $('#update_user_info').on('click', function() {
        // Collecting form data with validation
        let firstName = $('#firstnameInput').val().trim();
        let lastName = $('#lastnameInput').val().trim();
        let phone = $('#phonenumberInput').val().trim();
        let skill = $('#skillsInput').val();
        let joiningDate = $('#JoiningdatInput').val();
        let designation = $('#designationInput').val().trim();
        let city = $('#cityInput').val().trim();
        let country = $('#countryInput').val().trim();
        let zipCode = $('#zipcodeInput').val().trim();
        let description = $('#exampleFormControlTextarea').val().trim();

        // if (!firstName || !lastName || !phone || !joiningDate || !designation || !city || !country) {
        //     alert("Please fill in all required fields.");
        //     return;
        // }

        let formData = new FormData();
        formData.append('user_url', $('#profile-img-file-input')[0].files[0]);
        formData.append('first_name', firstName);
        formData.append('last_name', lastName);
        formData.append('phone', phone);
        formData.append('skill', skill);
        formData.append('joining_date', joiningDate);
        formData.append('designation', designation);
        formData.append('city', city);
        formData.append('country', country);
        formData.append('zip_code', zipCode);
        formData.append('description', description);
        formData.append('user_cover_url', $('#profile-foreground-img-file-input')[0].files[0]);
        
        $.ajax({
            url: 'update_user_info.php',
            type: 'POST',
            data: formData,
            // contentType: false,
            processData: false,
            success: function(response) {
                if (response.success) {
                    // Update display elements with new values
                    // $('#display_first_name').text(firstName);
                    // $('#display_last_name').text(lastName);
                    // $('#display_phone').text(phone);
                    // $('#display_skill').text(skill);
                    // $('#display_joining_date').text(joiningDate);
                    // $('#display_designation').text(designation);
                    // $('#display_city').text(city);
                    // $('#display_country').text(country);
                    // $('#display_zip_code').text(zipCode);
                    // $('#display_description').text(description);

                    // Update images if necessary
                    if (response.user_url) {
                        $('#display_profile_image').attr('src', response.user_url);
                    }
                    if (response.user_cover_url) {
                        $('#display_cover_image').attr('src', response.user_cover_url);
                    }

                    alert("User information updated successfully!");
                } else {
                    
                    alert("Failed to update user information.");
                }
            },
            error: function() {
                alert("An error occurred while updating user information.");
            }
        });
    });
});
