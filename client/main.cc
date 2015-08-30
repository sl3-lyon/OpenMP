#include <iostream>
#include <string>

#define COLOR_RED    "\e[31m"
#define COLOR_GREEN  "\e[32m"
#define COLOR_ORANGE "\e[33m"
#define RESET_COLOR  "\033[0m"

/**
 *
 * Options
 * -i <name>                # install
 * -r                       # remove
 * -s <name> [-v <version>] # search
 * -u <name>                # update
 * -h                       # help
 * -v                       # version
 */

int main(int argc, char **argv) {
	std::cout << "\e[31mTEST" << RESET_COLOR;
}

void print_success(std::string const& msg) {
	std::cout << COLOR_GREEN << "[ok] " << msg << RESET_COLOR;
}

void print_warning(std::string const& msg) {
	std::cout << COLOR_ORANGE	<< "[warning] " << msg << RESET_COLOR;
}

void print_error(std::string const& msg) {
	std::cout << COLOR_RED << "[error] " << msg << RESET_COLOR;
}
