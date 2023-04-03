from machina.fa import FiniteAutomata, State
from sys import argv
import xml.etree.ElementTree as ElementTree


def eat(xml_file: str) -> FiniteAutomata:
    tree = ElementTree.parse(xml_file)
    root = tree.getroot()

    fa = FiniteAutomata()

    content = root.find('automaton')
    for child in content:  # Needs to search for states and transitions.

        if child.tag == 'state':
            name = child.get('id')
            state = State(name)

            if len(child) != 2:  # Means the state is initial or final.
                tag: str = child[2].tag  # As the state's status.

                if tag == 'initial':
                    fa.initial_state = state

                if tag == 'final':
                    fa.final_states |= {state}

            fa.states |= {state}

        if child.tag == 'transition':
            src, dst, symbol = child[0].text, child[1].text, child[2].text
            src, dst = State(src), State(dst)  # To put the text as the labels for these states.
            fa.transitions[(src, symbol)] = {dst}

    return fa


if __name__ == '__main__':
    xml = argv[1]
    print(eat(xml))