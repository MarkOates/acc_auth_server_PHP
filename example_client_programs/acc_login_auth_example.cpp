/*
	This example program requires CURL
*/


#include <iostream>
#include <string>
#include <sstream>
#include <algorithm>

#include <curl/curl.h>


struct RequestData
{
	std::string auth_domain_name;
	std::string auth_key;
	std::string full_response;
	bool request_remotely_initialized;
};


std::string generate_auth_key(unsigned length = 32)
{
	srand(time(0));
	std::string selection_set;
	for (unsigned i=0; i<length; i++) selection_set += "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	std::random_shuffle(selection_set.begin(), selection_set.end());
	return selection_set.substr(0, length);
}


void launch_browser_to_login(RequestData *request_data)
{
	std::string validate_auth_url = request_data->auth_domain_name + "/validate_auth.php?auth_key=" + request_data->auth_key;

#ifdef _WIN32
	#include <windows.h>
	// this one is non-blocking
	ShellExecute(NULL, "open", validate_auth_url.c_str(),
		NULL, NULL, SW_SHOWNORMAL);
#elif defined __APPLE__
	std::stringstream command;
	command << "open " << validate_auth_url;
	system(command.str().c_str()); //< TODO: is this correct?
#else // assume a Linux environment
	std::stringstream command;
	command << "xdg-open " << validate_auth_url;
	system(command.str().c_str()); //< TODO: is this correct?
#endif
}



size_t CURL_buffer_write_callback(char *ptr, size_t size, size_t nmemb, void *userdata)
{
	std::string new_data;
	RequestData *request_data = static_cast<RequestData *>(userdata);
	for (unsigned i = 0; i < size*nmemb; i++)
		new_data.push_back(ptr[i]);

	std::cout << new_data; // comment this out for a "quiet" mode
	
	request_data->full_response += new_data;

	// check that the expected magical phrase has been provided.
	if (!request_data->request_remotely_initialized
		&& (request_data->full_response.substr(0, 52) == "Authorization request staged. Awaiting user login..."))
	{
		request_data->request_remotely_initialized = true;
		std::cout << std::endl << "Authorization request recieved by the server.  Launching browser..." << std::endl;
		launch_browser_to_login(request_data);
	}

	return size*nmemb;
}


int send_stage_auth_request_to_authority(std::string auth_domain_name)
{
	RequestData *request_data = new RequestData;
	request_data->auth_key = generate_auth_key();
	request_data->auth_domain_name = auth_domain_name;
	request_data->request_remotely_initialized = false;

	std::string auth_url = request_data->auth_domain_name + "/stage_auth_request.php?auth_key=" + request_data->auth_key;

	std::cout << "sending request to " << auth_url << std::endl;

	// start the curl operation
	CURL *curl = curl_easy_init();
	if (curl)
	{
		CURLcode res;
		curl_easy_setopt(curl, CURLOPT_WRITEFUNCTION, CURL_buffer_write_callback);
		curl_easy_setopt(curl, CURLOPT_WRITEDATA, request_data);
		curl_easy_setopt(curl, CURLOPT_URL, auth_url.c_str());
		res = curl_easy_perform(curl);
			// curl_easy_perform is blocking.  If this implementation were to be used in an a game,
			// then this send_stage_auth_request_to_authority() function should be executed as a thread.
			// that way the game could provide status feedback and/or play a 'waiting' animation.
			// Any status info be extracted during the CURL_buffer_write_callback and accessible
			// through a RequestData object (and should probably be sealed in a mutex)
		if (res == CURLE_OK)
		{
			std::cout << "curl request successfully completed." << std::endl;
			if (!request_data->request_remotely_initialized) std::cout << "ERROR: The authorization server did not respond as expected." << std::endl;
		}
		else
		{
			std::cout << "curl Error " << res << ": " << curl_easy_strerror(res) << std::endl;
		}
		curl_easy_cleanup(curl);
	}
	else
	{
		std::cerr << "Error: Could not initialize curl" << std::endl;
		return 1;
	}
	return 0;
}



int main(int argc, char* argv[])
{
	if (argc == 1)
	{
		std::cerr << "usage: acc_auth_ex http://www.someauthdomain.cc" << std::endl;
		return 1;
	}

	std::cout << "Are you ready? Ready to start the authorization process? :D Press ENTER and you will be wisked away to the authorization page for " << argv[1] << " on your browser." << std::endl;
	std::cin.ignore();

	send_stage_auth_request_to_authority(argv[1]);
}
