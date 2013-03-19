<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use SmartCore\Bundle\EngineBundle\Entity\Block;
use SmartCore\Bundle\EngineBundle\Entity\Folder;
use SmartCore\Bundle\EngineBundle\Entity\Node;
use SmartCore\Bundle\EngineBundle\Form\Type\BlockFormType;
use SmartCore\Bundle\EngineBundle\Form\Type\FolderFormType;
use SmartCore\Bundle\EngineBundle\Form\Type\NodeFormType;

class AdminStructureController extends Controller
{
    public function indexAction(Request $request)
    {
        return $this->renderView('SmartCoreEngineBundle:Admin:structure.html.twig', array(

        ));
    }

    public function nodeAction(Request $request)
    {
        return $this->renderView('SmartCoreEngineBundle:Admin:structure.html.twig', array(

        ));
    }

    public function nodeCreateAction(Request $request, $folder_pid = 1)
    {
        $em = $this->EM();

        $node = new Node();
        $node->setCreateByUserId($this->getUser()->getId());
        $node->setFolder($em->find('SmartCoreEngineBundle:Folder', $folder_pid));

        $form = $this->createForm(new NodeFormType(), $node);

        if ($request->isMethod('POST')) {
            if ($request->request->has('create')) {
                $form->bind($request);
                if ($form->isValid()) {
                    $em->persist($form->getData());
                    $em->flush();

                    $this->get('session')->getFlashBag()->add('notice', 'Нода создана.');
                    return $this->redirect($this->generateUrl('cmf_admin_structure'));
                }
            } else if ($request->request->has('delete')) {
                die('@todo');
            }
        }

        return $this->renderView('SmartCoreEngineBundle:Admin:structure_edit.html.twig', array(
            'html_head_title' => 'Create node',
            'form' => $form->createView(),
            'form_action' => $this->generateUrl('cmf_admin_structure_node_create'),
            'form_controls' => 'create',
        ));
    }

    /**
     * Редактирование папки.
     */
    public function folderEditAction(Request $request, $id = 1)
    {
        $em = $this->EM();
        $folder = $em->find('SmartCoreEngineBundle:Folder', $id);

        if (empty($folder)) {
            return $this->redirect($this->generateUrl('cmf_admin_structure'));
        }

        $form = $this->createForm(new FolderFormType(), $folder);

        // Для корневой папки удаляются некоторые поля формы
        if (1 == $id) {
            $form
                ->remove('uri_part')
                ->remove('parent_folder')
                ->remove('is_active')
                ->remove('is_file')
                ->remove('pos');
        }

        if ($request->isMethod('POST')) {
            if ($request->request->has('update')) {
                $form->bind($request);
                if ($form->isValid()) {
                    $em = $this->EM();
                    $em->persist($form->getData());
                    $em->flush();

                    $this->get('session')->getFlashBag()->add('notice', 'Папка обновлена.');
                    return $this->redirect($this->generateUrl('cmf_admin_structure'));
                }
            } else if ($request->request->has('delete')) {
                die('@todo');
            }
        }

        return $this->renderView('SmartCoreEngineBundle:Admin:structure_edit.html.twig', array(
            'folder_id' => $id,
            'html_head_title' => 'Edit folder',
            'form' => $form->createView(),
            'form_action' => $this->generateUrl('cmf_admin_structure_folder', array('id' => $id)),
            'form_controls' => 'update',
        ));
    }

    /**
     * Создание папки.
     */
    public function folderCreateAction(Request $request, $folder_pid = 1)
    {
        $em = $this->EM();

        $folder = new Folder();
        $folder->setCreateByUserId($this->getUser()->getId());
        $folder->setParentFolder($em->find('SmartCoreEngineBundle:Folder', $folder_pid));

        $form = $this->createForm(new FolderFormType(), $folder);

        if ($request->isMethod('POST')) {
            if ($request->request->has('create')) {
                $form->bind($request);
                if ($form->isValid()) {
                    $em->persist($form->getData());
                    $em->flush();

                    $this->get('session')->getFlashBag()->add('notice', 'Папка создана.');
                    return $this->redirect($this->generateUrl('cmf_admin_structure'));
                }
            } else if ($request->request->has('delete')) {
                die('@todo');
            }
        }

        return $this->renderView('SmartCoreEngineBundle:Admin:structure_edit.html.twig', array(
            'html_head_title' => 'Create folder',
            'form' => $form->createView(),
            'form_action' => $this->generateUrl('cmf_admin_structure_folder_create'),
            'form_controls' => 'create',
        ));
    }

    /**
     * Отображение списка всех блоков, а также форма добавления нового.
     *
     * @param Request $request
     * @param int $id
     */
    public function blockAction(Request $request, $id = 0)
    {
        $em = $this->EM();
        $block = $em->find('SmartCoreEngineBundle:Block', $id);

        if (empty($block)) {
            $block = new Block();
            $block->setCreateByUserId($this->getUser()->getId());
        }

        $form = $this->createForm(new BlockFormType(), $block);

        if ($request->isMethod('POST')) {
            if ($request->request->has('create') or $request->request->has('update')) {
                $form->bind($request);
                if ($form->isValid()) {
                    $em->persist($form->getData());
                    $em->flush();

                    if ($request->request->has('create')) {
                        $notice = 'Блок создан.';
                    } else if ($request->request->has('update')) {
                        $notice = 'Блок обновлён.';
                    }

                    $this->get('session')->getFlashBag()->add('notice', $notice);
                    return $this->redirect($this->generateUrl('cmf_admin_structure_block'));
                }
            } else if ($request->request->has('delete')) {
                $em->remove($form->getData());
                $em->flush();

                $this->get('session')->getFlashBag()->add('notice', 'Блок удалён.');
                return $this->redirect($this->generateUrl('cmf_admin_structure_block'));
            }
        }

        if ($id) {
            $arg = array(
                'block_id'  => $id,
                'form_edit' => $form->createView(),
            );
        } else {
            $arg = array(
                'all_blocks'  => $em->getRepository('SmartCoreEngineBundle:Block')->findBy(array(), array('position' => 'ASC')),
                'form_create' => $form->createView(),
            );
        }

        return $this->renderView('SmartCoreEngineBundle:Admin:block.html.twig', $arg);
    }

    /**
     * Отображение структуры в виде дерева.
     */
    public function showTreeAction($id = null)
    {
        return $this->renderView('SmartCoreEngineBundle:Admin:tree.html.twig', array(
            'folder_id' => $id,
        ));
    }
}
